<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceStudent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicAttendanceController extends Controller
{
    public function index()
    {
        return view('public.attendance.index');
    }

    public function check(Request $request)
    {
        $request->validate([
            'nis' => 'required|string',
            'date' => 'required|date',
        ]);

        $student = Student::with('classRoom')->where('nis', $request->nis)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Siswa dengan NIS ' . $request->nis . ' tidak ditemukan.'
            ], 404);
        }

        $date = Carbon::parse($request->date)->startOfDay();
        $today = Carbon::today();
        
        $attendance = AttendanceStudent::with('attendanceCode', 'shift')
            ->where('student_id', $student->id)
            ->whereDate('dates', $date)
            ->first();

        $result = [
            'student_name' => $student->name,
            'class_name' => $student->classRoom->name ?? '-',
            'date' => $date->translatedFormat('l, d F Y'),
            'status' => '-',
            'check_in' => '-',
            'check_out' => '-',
            'note' => '-',
        ];

        if ($attendance) {
            $statusName = $attendance->attendanceCode->name ?? 'Hadir';
            $result['status'] = $statusName;
            
            // Hanya tampilkan jam jika statusnya 'Hadir'
            if ($statusName === 'Hadir') {
                $result['check_in'] = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '-';
                $result['check_out'] = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '-';
            } else {
                $result['check_in'] = '-';
                $result['check_out'] = '-';
            }
            
            $result['note'] = $attendance->note ?? '-';
        } else {
            // Logic jika tidak ada data di tabel attendance
            if ($date->gt($today)) {
                $result['status'] = 'Belum Tersedia';
            } elseif ($date->eq($today)) {
                $result['status'] = 'Belum Absensi';
            } else {
                $result['status'] = 'Alpa';
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
