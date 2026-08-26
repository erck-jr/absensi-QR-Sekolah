<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CardTemplate;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\IdCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardGeneratorController extends Controller
{
    protected $idCardService;

    public function __construct(IdCardService $idCardService)
    {
        $this->idCardService = $idCardService;
    }

    public function index()
    {
        $classes = SchoolClass::all();
        return view('admin.generator.index', compact('classes'));
    }

    public function getStudents(Request $request)
    {
        $query = Student::query();
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        $students = $query->pluck('id');
        return response()->json($students);
    }

    public function getTeachers()
    {
        $teachers = Teacher::pluck('id');
        return response()->json($teachers);
    }

    // =========================================================================
    // SINGLE / OVERWRITE GENERATE (dipanggil dari halaman profil siswa/guru)
    // Selalu generate ulang (overwrite) tanpa cek cache.
    // =========================================================================

    public function generateStudent(Request $request)
    {
        $request->validate(['id' => 'required|exists:students,id']);
        $student = Student::with('classRoom')->find($request->id);

        // Mode overwrite = true (single generate dari profil)
        $result = $this->idCardService->generateStudentCard($student, true);

        // Sinkronisasi cache: tambahkan/update id di cached_idcard agar tetap sinkron
        if ($result['success']) {
            $this->addStudentToCache($student->id);
        }

        return response()->json($result);
    }

    public function generateTeacher(Request $request)
    {
        $request->validate(['id' => 'required|exists:teachers,id']);
        $teacher = Teacher::find($request->id);

        // Mode overwrite = true (single generate dari profil)
        $result = $this->idCardService->generateTeacherCard($teacher, true);

        // Sinkronisasi cache
        if ($result['success']) {
            $this->addTeacherToCache($teacher->id);
        }

        return response()->json($result);
    }

    // =========================================================================
    // MASS / CACHED GENERATE (dipanggil dari halaman index generator via AJAX loop)
    // Bersifat cached: skip generate jika ID sudah ada di cached_idcard.
    // =========================================================================

    public function massGenerateStudent(Request $request)
    {
        $request->validate(['id' => 'required|exists:students,id']);
        $student = Student::with('classRoom')->find($request->id);

        // Cek cache: apakah student ini sudah pernah digenerate?
        $template   = CardTemplate::where('key', 'student_front')->first();
        $cachedIds  = $template ? ($template->cached_idcard ?? []) : [];

        if (in_array($student->id, $cachedIds)) {
            // Sudah ter-cache, tidak perlu generate ulang
            return response()->json([
                'success' => true,
                'cached'  => true,
                'message' => 'ID Card sudah ter-cache, tidak digenerate ulang.',
            ]);
        }

        // Belum ter-cache, jalankan generate dengan mode cached (isOverwrite = false)
        $result = $this->idCardService->generateStudentCard($student, false);

        if ($result['success']) {
            // Tambahkan student ID ke cached_idcard
            $this->addStudentToCache($student->id, $template);
        }

        return response()->json($result);
    }

    public function massGenerateTeacher(Request $request)
    {
        $request->validate(['id' => 'required|exists:teachers,id']);
        $teacher = Teacher::find($request->id);

        // Cek cache: apakah teacher ini sudah pernah digenerate?
        $template  = CardTemplate::where('key', 'teacher_front')->first();
        $cachedIds = $template ? ($template->cached_idcard ?? []) : [];

        if (in_array($teacher->id, $cachedIds)) {
            // Sudah ter-cache, tidak perlu generate ulang
            return response()->json([
                'success' => true,
                'cached'  => true,
                'message' => 'ID Card sudah ter-cache, tidak digenerate ulang.',
            ]);
        }

        // Belum ter-cache, jalankan generate dengan mode cached (isOverwrite = false)
        $result = $this->idCardService->generateTeacherCard($teacher, false);

        if ($result['success']) {
            // Tambahkan teacher ID ke cached_idcard
            $this->addTeacherToCache($teacher->id, $template);
        }

        return response()->json($result);
    }

    // =========================================================================
    // DOWNLOAD ZIP
    // =========================================================================

    public function downloadZip(Request $request)
    {
        $type    = $request->type;    // 'student' or 'teacher'
        $classId = $request->class_id;

        $zipName = "id_cards_{$type}_" . now()->format('Ymd_His') . ".zip";
        $zipPath = storage_path("app/public/temp/{$zipName}");

        if (!file_exists(storage_path("app/public/temp"))) {
            mkdir(storage_path("app/public/temp"), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Gagal membuat file ZIP.');
        }

        $addedCount = 0;

        if ($type === 'student') {
            // Tentukan folder pencarian berdasarkan class_id atau semua
            if ($classId) {
                $class = SchoolClass::find($classId);
                if ($class) {
                    $sanitizedClassName = $this->sanitizeForSearch($class->name);
                    $folderPath = storage_path("app/public/id_cards/students/{$sanitizedClassName}");
                    $addedCount = $this->addFolderToZip($zip, $folderPath, "students/{$sanitizedClassName}");
                }
            } else {
                // Semua kelas: scan semua subfolder di id_cards/students/
                $studentsRoot = storage_path("app/public/id_cards/students");
                if (is_dir($studentsRoot)) {
                    $classDirs = glob($studentsRoot . '/*', GLOB_ONLYDIR);
                    foreach ($classDirs as $classDir) {
                        $classDirName = basename($classDir);
                        $addedCount += $this->addFolderToZip($zip, $classDir, "students/{$classDirName}");
                    }
                }
            }
        } else {
            // Teacher: scan folder id_cards/teachers/
            $teachersFolder = storage_path("app/public/id_cards/teachers");
            $addedCount     = $this->addFolderToZip($zip, $teachersFolder, "teachers");
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            return back()->with('error', 'Tidak ada file ID Card yang ditemukan untuk diunduh. Silakan generate terlebih dahulu.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Tambahkan student ID ke array cached_idcard pada template student_front.
     */
    private function addStudentToCache(int $studentId, ?CardTemplate $template = null): void
    {
        $template  = $template ?? CardTemplate::where('key', 'student_front')->first();
        if (!$template) return;

        $cachedIds = $template->cached_idcard ?? [];
        if (!in_array($studentId, $cachedIds)) {
            $cachedIds[] = $studentId;
            $template->update(['cached_idcard' => $cachedIds]);
        }
    }

    /**
     * Tambahkan teacher ID ke array cached_idcard pada template teacher_front.
     */
    private function addTeacherToCache(int $teacherId, ?CardTemplate $template = null): void
    {
        $template  = $template ?? CardTemplate::where('key', 'teacher_front')->first();
        if (!$template) return;

        $cachedIds = $template->cached_idcard ?? [];
        if (!in_array($teacherId, $cachedIds)) {
            $cachedIds[] = $teacherId;
            $template->update(['cached_idcard' => $cachedIds]);
        }
    }

    /**
     * Tambahkan semua file PNG dalam sebuah folder ke dalam ZIP.
     * Mengembalikan jumlah file yang berhasil ditambahkan.
     */
    private function addFolderToZip(\ZipArchive $zip, string $folderPath, string $zipSubDir): int
    {
        $count = 0;
        if (!is_dir($folderPath)) return $count;

        $files = glob($folderPath . '/*.png');
        foreach ($files as $file) {
            $zip->addFile($file, $zipSubDir . '/' . basename($file));
            $count++;
        }
        return $count;
    }

    /**
     * Sanitasi nama untuk keperluan pencarian folder (harus konsisten dengan sanitizeFileName di service).
     */
    private function sanitizeForSearch(string $string): string
    {
        $string = preg_replace('/[^A-Za-z0-9\s_\-]/', '', $string);
        $string = preg_replace('/\s+/', '_', trim($string));
        return $string;
    }
}
