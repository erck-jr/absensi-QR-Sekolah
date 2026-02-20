<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStudent;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        // 1. Top Cards Data
        $totalStudents = Student::count();
        $totalTeachers = \App\Models\Teacher::count();
        $totalClasses = \App\Models\SchoolClass::count();
        $totalUsers = \App\Models\User::count();

        // 2. Daily Attendance (Students)
        $studentAttendance = AttendanceStudent::whereDate('dates', $today)->with('attendanceCode')->get();
        $studentHadir = $studentAttendance->where('attendanceCode.name', 'Hadir')->count();
        $studentSakit = $studentAttendance->where('attendanceCode.name', 'Sakit')->count();
        $studentIzin = $studentAttendance->where('attendanceCode.name', 'Izin')->count();
        // Alpa is total students minus those with a record (assuming records strictly mean presence/excuse)
        $studentAlpa = $totalStudents - ($studentHadir + $studentSakit + $studentIzin);

        // 3. Daily Attendance (Teachers)
        $teacherAttendance = \App\Models\AttendanceTeacher::whereDate('dates', $today)->with('attendanceCode')->get();
        $teacherHadir = $teacherAttendance->where('attendanceCode.name', 'Hadir')->count();
        $teacherSakit = $teacherAttendance->where('attendanceCode.name', 'Sakit')->count();
        $teacherIzin = $teacherAttendance->where('attendanceCode.name', 'Izin')->count();
        $teacherAlpa = $totalTeachers - ($teacherHadir + $teacherSakit + $teacherIzin);

        // 4. 7-Day Trend (Students) - Count 'H' only
        $studentTrend = [];
        $teacherTrend = [];
        $dates = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $formattedDate = $date->format('d M');
            $dates[] = $formattedDate;

            $studentTrend[] = AttendanceStudent::whereDate('dates', $date)
                ->whereHas('attendanceCode', fn($q) => $q->where('name', 'Hadir'))
                ->count();
            
            $teacherTrend[] = \App\Models\AttendanceTeacher::whereDate('dates', $date)
                ->whereHas('attendanceCode', fn($q) => $q->where('name', 'Hadir'))
                ->count();
        }

        return view('dashboard', compact(
            'totalStudents', 'totalTeachers', 'totalClasses', 'totalUsers',
            'studentHadir', 'studentSakit', 'studentIzin', 'studentAlpa',
            'teacherHadir', 'teacherSakit', 'teacherIzin', 'teacherAlpa',
            'dates', 'studentTrend', 'teacherTrend'
        ));
    }
}
