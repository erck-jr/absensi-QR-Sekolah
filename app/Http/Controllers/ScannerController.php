<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCode;
use App\Models\AttendanceStudent;
use App\Models\AttendanceTeacher;
use App\Models\Shift;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\WaLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScannerController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        return view('scanner.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unique_code' => 'required|string',
            'shift_id' => 'required|exists:shifts,id',
            'mode' => 'required|in:in,out',
        ]);

        $shift = Shift::findOrFail($request->shift_id);
        $mode = $request->mode;
        $user = null;
        $type = '';

        // Find User
        if ($student = Student::where('unique_code', $request->unique_code)->first()) {
            $user = $student;
            $type = 'student';
        } elseif ($teacher = Teacher::where('unique_code', $request->unique_code)->first()) {
            $user = $teacher;
            $type = 'teacher';
        } else {
            return response()->json(['status' => 'error', 'message' => 'QR Code tidak dikenali.'], 404);
        }

        // Check for existing attendance today
        $today = Carbon::today();
        $isStudent = ($type === 'student');

        $attendance = $isStudent
            ? AttendanceStudent::where('student_id', $user->id)->whereDate('dates', $today)->first()
            : AttendanceTeacher::where('teacher_id', $user->id)->whereDate('dates', $today)->first();

        DB::beginTransaction();
        try {
            $now = Carbon::now();
            
            // --- LOGIC MODE MASUK ---
            if ($mode === 'in') {
                if ($attendance) {
                    return response()->json([
                        'status' => 'warning', 
                        'message' => 'Anda sudah melakukan absen masuk hari ini.',
                        'data' => [
                            'name' => $user->name,
                            'time' => Carbon::parse($attendance->check_in)->format('H:i:s'),
                            'status' => $attendance->is_late ? 'Terlambat' : 'Tepat Waktu'
                        ]
                    ], 422);
                }

                // Determine Status (Late/Hadir)
                $checkInLimit = Carbon::parse($shift->check_in_time)->addMinutes($shift->late_check_in_minute);
                $isLate = $now->gt($checkInLimit);
                
                $code = AttendanceCode::where('name', 'Hadir')->first();
                if (!$code) {
                    return response()->json(['status' => 'error', 'message' => 'Kode absensi "Hadir" belum disetting.'], 500);
                }

                if ($isStudent) {
                    $attendance = AttendanceStudent::create([
                        'student_id' => $user->id,
                        'shift_id' => $shift->id,
                        'attendance_id' => $code->id,
                        'dates' => $today,
                        'check_in' => $now->format('H:i:s'),
                        'is_late' => $isLate,
                    ]);
                } else {
                    $attendance = AttendanceTeacher::create([
                        'teacher_id' => $user->id,
                        'shift_id' => $shift->id,
                        'attendance_id' => $code->id,
                        'dates' => $today,
                        'check_in' => $now->format('H:i:s'),
                        'is_late' => $isLate,
                    ]);
                }

                \App\Jobs\SendAttendanceWA::dispatch($attendance, $type, 'check_in', $isLate, false);

                DB::commit();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Absensi Masuk berhasil!',
                    'data' => [
                        'name' => $user->name,
                        'time' => $now->format('H:i:s'),
                        'status' => $isLate ? 'Terlambat' : 'Tepat Waktu'
                    ]
                ]);
            }

            // --- LOGIC MODE PULANG ---
            if ($mode === 'out') {
                if (!$attendance) {
                    return response()->json(['status' => 'error', 'message' => 'Anda belum melakukan absen masuk.'], 422);
                }

                if ($attendance->check_out) {
                    return response()->json([
                        'status' => 'warning', 
                        'message' => 'Anda sudah melakukan absen pulang hari ini.',
                        'data' => [
                            'name' => $user->name,
                            'time' => Carbon::parse($attendance->check_out)->format('H:i:s'),
                            'status' => Carbon::parse($attendance->check_out)->lt(Carbon::parse($shift->check_out_time)) ? 'Pulang Cepat' : 'Pulang Normal'
                        ]
                    ], 422);
                }

                // Perform Check-Out
                $attendance->update(['check_out' => $now->format('H:i:s')]);

                // Determine Early Checkout
                $checkOutTime = Carbon::parse($shift->check_out_time);
                $isEarly = $now->lt($checkOutTime);

                // Dispatch Job
                \App\Jobs\SendAttendanceWA::dispatch($attendance, $type, 'check_out', false, $isEarly);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Absensi Pulang berhasil!',
                    'data' => [
                        'name' => $user->name,
                        'time' => $now->format('H:i:s'),
                        'status' => $isEarly ? 'Pulang Cepat' : 'Pulang Normal'
                    ]
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}
