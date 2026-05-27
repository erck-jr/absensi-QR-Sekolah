<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AcademicYearController extends Controller
{
    /**
     * Show the Academic Year and Database Management page
     */
    public function index()
    {
        // 1. Get levels sorted by level_order, along with their classes
        $levels = Level::with('classes')->orderBy('level_order')->get();

        // 2. Map levels to find recommended next level for promotion dropdowns
        $levelMapping = [];
        foreach ($levels as $index => $level) {
            // Find level with the next higher order
            $nextLevel = Level::where('level_order', '>', $level->level_order)
                ->orderBy('level_order')
                ->first();

            $levelMapping[$level->id] = [
                'current' => $level,
                'next' => $nextLevel,
                'next_classes' => $nextLevel ? SchoolClass::where('level_id', $nextLevel->id)->get() : collect(),
            ];
        }

        // 3. Get backup files list
        $backups = [];
        if (Storage::exists('backups')) {
            $files = Storage::files('backups');
            foreach ($files as $file) {
                $filename = basename($file);
                
                // Determine backup type
                $type = 'Full Backup';
                if (str_contains($filename, 'guests')) {
                    $type = 'Buku Tamu';
                }

                $backups[] = [
                    'filename' => $filename,
                    'type' => $type,
                    'size' => $this->formatBytes(Storage::size($file)),
                    'date' => date('d/m/Y H:i:s', Storage::lastModified($file)),
                    'timestamp' => Storage::lastModified($file)
                ];
            }
        }

        // Sort backups by latest first
        usort($backups, function ($a, $b) {
            return $b['timestamp'] - $a['timestamp'];
        });

        return view('settings.academic_year.index', compact('levels', 'levelMapping', 'backups'));
    }

    /**
     * Promote student classes in bulk
     */
    public function promote(Request $request)
    {
        $request->validate([
            'class_map' => 'required|array',
        ]);

        $classMap = $request->input('class_map'); // Array: [source_class_id => target_class_id / 'lulus']
        $promotionCount = 0;
        $graduationCount = 0;

        try {
            DB::transaction(function () use ($classMap, &$promotionCount, &$graduationCount) {
                // 1. Ambil data siswa aktif di masing-masing kelas secara terpisah ke dalam memori
                // untuk menghindari efek cascade update beruntun
                $studentsByClass = [];
                foreach ($classMap as $sourceClassId => $targetValue) {
                    $studentsByClass[$sourceClassId] = Student::where('class_id', $sourceClassId)->get();
                }

                // 2. Eksekusi pemindahan/kelulusan berdasarkan data siswa asli
                foreach ($classMap as $sourceClassId => $targetValue) {
                    $students = $studentsByClass[$sourceClassId] ?? collect();
                    
                    if ($students->isEmpty()) {
                        continue;
                    }

                    if ($targetValue === 'lulus') {
                        // Soft delete siswa di kelas ini
                        foreach ($students as $student) {
                            $student->delete(); // Memicu soft delete
                            $graduationCount++;
                        }
                    } elseif (is_numeric($targetValue)) {
                        // Pindahkan siswa ke kelas baru menggunakan ID spesifik (bukan class_id)
                        $studentIds = $students->pluck('id')->toArray();
                        $affected = Student::whereIn('id', $studentIds)
                            ->update(['class_id' => $targetValue]);
                        
                        $promotionCount += $affected;
                    }
                }
            });

            Log::info("Kenaikan Kelas Massal Berhasil: {$promotionCount} siswa dipromosikan, {$graduationCount} siswa diluluskan.");

            return redirect()->route('academic.year.index')->with('success', "Kenaikan kelas berhasil! {$promotionCount} siswa dipromosikan, dan {$graduationCount} siswa diluluskan (alumni).");
        } catch (\Exception $e) {
            Log::error("Gagal melakukan kenaikan kelas massal: " . $e->getMessage());
            return redirect()->route('academic.year.index')->with('error', "Gagal memproses kenaikan kelas: " . $e->getMessage());
        }
    }

    /**
     * Create full database & guest backup, then truncate attendance data
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'confirm_checkbox' => 'required',
            'confirm_text' => 'required|string',
        ]);

        if (trim($request->confirm_text) !== 'BERSIHKAN DATABASE ABSENSI') {
            return redirect()->route('academic.year.index')->with('error', 'Konfirmasi teks tidak cocok. Proses pembersihan dibatalkan.');
        }

        $timestamp = date('Y-m-d_H-i-s');
        $fullBackupFile = "backup_full_{$timestamp}.sql";
        $guestsBackupFile = "backup_guests_{$timestamp}.sql";

        try {
            // 1. Create backups using Native BackupService
            BackupService::backupFull($fullBackupFile);
            BackupService::backupGuests($guestsBackupFile);

            // 2. Perform table cleanups (no transaction since TRUNCATE commits implicitly in MySQL)
            DB::statement("SET FOREIGN_KEY_CHECKS=0;");
            DB::table('wa_logs')->truncate();
            DB::table('guests')->truncate();
            DB::table('attendance_students')->truncate();
            DB::table('attendance_teachers')->truncate();
            DB::statement("SET FOREIGN_KEY_CHECKS=1;");

            Log::warning("Pembersihan data absensi tahunan berhasil dilakukan oleh User ID: " . auth()->id());

            // Get download link
            $downloadLink = route('academic.year.download', $fullBackupFile);

            return redirect()->route('academic.year.index')->with('success_cleanup', [
                'message' => 'Data absensi, log WA, dan buku tamu telah berhasil dibersihkan dari database.',
                'download_url' => $downloadLink,
                'filename' => $fullBackupFile
            ]);
        } catch (\Exception $e) {
            Log::error("Gagal melakukan pembersihan database: " . $e->getMessage());
            return redirect()->route('academic.year.index')->with('error', 'Gagal memproses pembersihan: ' . $e->getMessage());
        }
    }

    /**
     * Restore Guest data from file
     */
    public function restoreGuests(Request $request, $filename)
    {
        $request->validate([
            'restore_confirm_text' => 'required|string'
        ]);

        if (trim($request->restore_confirm_text) !== 'RESTORE TAMU') {
            return redirect()->route('academic.year.index')->with('error', 'Konfirmasi teks restore tamu tidak cocok.');
        }

        try {
            BackupService::restoreGuests($filename);
            return redirect()->route('academic.year.index')->with('success', 'Data buku tamu berhasil dikembalikan dari backup.');
        } catch (\Exception $e) {
            return redirect()->route('academic.year.index')->with('error', 'Gagal restore data tamu: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file securely
     */
    public function downloadBackup($filename)
    {
        $filePath = 'backups/' . $filename;
        if (!Storage::exists($filePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        return Storage::download($filePath);
    }

    /**
     * Delete backup file
     */
    public function deleteBackup($filename)
    {
        $filePath = 'backups/' . $filename;
        if (!Storage::exists($filePath)) {
            return redirect()->route('academic.year.index')->with('error', 'File tidak ditemukan.');
        }

        Storage::delete($filePath);
        return redirect()->route('academic.year.index')->with('success', 'File backup berhasil dihapus dari server.');
    }

    /**
     * Format file size helper
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
