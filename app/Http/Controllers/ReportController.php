<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStudent;
use App\Models\AttendanceTeacher;
use App\Models\SchoolClass;
use App\Models\Holiday; // Import Holiday
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(\App\Services\ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function studentIndex(Request $request)
    {
        $classes = SchoolClass::all();
        $attendanceCodes = \App\Models\AttendanceCode::all();
        $shifts = \App\Models\Shift::all();

        $mode = $request->get('mode', 'daily');
        
        if ($mode === 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year); 

            $currentDate = Carbon::now();
            $selectedDate = Carbon::createFromDate($year, $month, 1);
            
            if ($selectedDate->startOfMonth()->gte($currentDate->startOfMonth())) {
                 $reportData = null; // No Data
                 $isFutureOrCurrent = true;
            } else {
                 $isFutureOrCurrent = false;
                 
                 // Validate Class ID for Monthly Mode
                 if (!$request->filled('class_id')) {
                    return redirect()->route('reports.students', ['mode' => 'daily'])->with('error', 'Silakan pilih kelas untuk melihat laporan bulanan.');
                 }

                 // Fetch Students
                 $query = \App\Models\Student::with(['classRoom', 'attendances' => function($q) use ($month, $year) {
                     $q->whereMonth('dates', $month)->whereYear('dates', $year)->with('attendanceCode');
                 }]);

                 if ($request->filled('class_id')) {
                    $query->where('class_id', $request->class_id);
                 }
                 
                 // Get Data
                 $students = $query->orderBy('name')->get();
                 
                 // Generate Report Data via Service
                 $reportData = $this->reportService->generateMonthlyData($students, $month, $year);
            }

            return view('reports.students', compact('reportData', 'classes', 'attendanceCodes', 'shifts', 'mode', 'month', 'year', 'isFutureOrCurrent'));
        }

        // DAILY MODE
        $date = $request->get('start_date', Carbon::today()->toDateString());
        $isSunday = Carbon::parse($date)->isSunday();
        $holiday = Holiday::whereDate('dates', $date)->first();
        
        // Query Students (Show ALL)
        $query = \App\Models\Student::with(['classRoom', 'attendances' => function($q) use ($date) {
            $q->whereDate('dates', $date)->with('attendanceCode', 'shift');
        }]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        if ($request->filled('status')) {
             $status = $request->status;
             $isCode = \App\Models\AttendanceCode::where('name', $status)->exists();
             
             if ($isCode) {
                 $query->whereHas('attendances', function($q) use ($date, $status) {
                     $q->whereDate('dates', $date)->whereHas('attendanceCode', fn($sq) => $sq->where('name', $status));
                 });
             } elseif ($status === 'Alpha' || $status === 'Belum Absensi' || $status === 'Belum Tersedia') {
                  $query->whereDoesntHave('attendances', function($q) use ($date) {
                      $q->whereDate('dates', $date);
                  });
             }
        }

        $students = $query->orderBy('name')->get();

        return view('reports.students', compact('students', 'classes', 'attendanceCodes', 'shifts', 'date', 'mode', 'isSunday', 'holiday'));
    }

    public function teacherIndex(Request $request)
    {
        $attendanceCodes = \App\Models\AttendanceCode::all();
        $shifts = \App\Models\Shift::all();
        $mode = $request->get('mode', 'daily');

        if ($mode === 'monthly') {
            $month = $request->get('month', Carbon::now()->month);
            $year = $request->get('year', Carbon::now()->year);

            $currentDate = Carbon::now();
            $selectedDate = Carbon::createFromDate($year, $month, 1);

            if ($selectedDate->startOfMonth()->gte($currentDate->startOfMonth())) {
                 $reportData = null;
                 $isFutureOrCurrent = true;
            } else {
                 $isFutureOrCurrent = false;
                 $query = \App\Models\Teacher::with(['attendances' => function($q) use ($month, $year) {
                     $q->whereMonth('dates', $month)->whereYear('dates', $year)->with('attendanceCode');
                 }]);
                 
                 // Get Data
                 $teachers = $query->orderBy('name')->get();

                 // Generate Report Data via Service
                 $reportData = $this->reportService->generateMonthlyData($teachers, $month, $year);
            }

            return view('reports.teachers', compact('reportData', 'attendanceCodes', 'shifts', 'mode', 'month', 'year', 'isFutureOrCurrent'));
        }

        // DAILY
        $date = $request->get('start_date', Carbon::today()->toDateString());
        $isSunday = Carbon::parse($date)->isSunday();
        $holiday = Holiday::whereDate('dates', $date)->first();

        $query = \App\Models\Teacher::with(['attendances' => function($q) use ($date) {
            $q->whereDate('dates', $date)->with('attendanceCode', 'shift');
        }]);
        
        if ($request->filled('status')) {
             $status = $request->status;
             $isCode = \App\Models\AttendanceCode::where('name', $status)->exists();
             if ($isCode) {
                 $query->whereHas('attendances', function($q) use ($date, $status) {
                     $q->whereDate('dates', $date)->whereHas('attendanceCode', fn($sq) => $sq->where('name', $status));
                 });
             } elseif ($status === 'Alpha' || $status === 'Belum Absensi') {
                  $query->whereDoesntHave('attendances', function($q) use ($date) {
                      $q->whereDate('dates', $date);
                  });
             }
        }



        $teachers = $query->orderBy('name')->get();
        
        return view('reports.teachers', compact('teachers', 'attendanceCodes', 'shifts', 'date', 'mode', 'isSunday', 'holiday'));
    }

    public function updateStudentAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'attendance_code_id' => 'required|exists:attendance_codes,id',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'required', // Needed for DB constraint
            'note' => 'nullable|string'
        ]);

        AttendanceStudent::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'dates' => $request->date
            ],
            [
                'attendance_id' => $request->attendance_code_id,
                'shift_id' => $request->shift_id,
                'check_in' => $request->check_in,
                'is_late' => false, // Default logic or calculation? Manual update usually overrides lateness or sets to false unless specified.
                'note' => $request->note
            ]
        );

        return back()->with('success', 'Status absensi siswa berhasil diperbarui.');
    }

    public function updateTeacherAttendance(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'attendance_code_id' => 'required|exists:attendance_codes,id',
            'shift_id' => 'required|exists:shifts,id',
            'check_in' => 'required',
            'note' => 'nullable|string'
        ]);

        AttendanceTeacher::updateOrCreate(
            [
                'teacher_id' => $request->teacher_id,
                'dates' => $request->date
            ],
            [
                'attendance_id' => $request->attendance_code_id,
                'shift_id' => $request->shift_id,
                'check_in' => $request->check_in,
                'is_late' => false,
                'note' => $request->note
            ]
        );

        return back()->with('success', 'Status absensi guru berhasil diperbarui.');
    }
    public function exportStudentMonthly(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $query = \App\Models\Student::with(['classRoom', 'attendances' => function($q) use ($month, $year) {
             $q->whereMonth('dates', $month)->whereYear('dates', $year)->with('attendanceCode');
         }]);

         if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
         }
         
         $students = $query->orderBy('name')->get();
         $reportData = $this->reportService->generateMonthlyData($students, $month, $year);

         return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentMonthlyExport($reportData, $month, $year), 'Laporan_Siswa_' . $month . '_' . $year . '.xlsx');
    }

    public function exportTeacherMonthly(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

         $query = \App\Models\Teacher::with(['attendances' => function($q) use ($month, $year) {
             $q->whereMonth('dates', $month)->whereYear('dates', $year)->with('attendanceCode');
         }]);
         
         $teachers = $query->orderBy('name')->get();
         $reportData = $this->reportService->generateMonthlyData($teachers, $month, $year);

         return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TeacherMonthlyExport($reportData, $month, $year), 'Laporan_Guru_' . $month . '_' . $year . '.xlsx');
    }

    public function exportStudentDaily(Request $request)
    {
        $date = $request->get('start_date', Carbon::today()->toDateString());
        
        $query = \App\Models\Student::with(['classRoom', 'attendances' => function($q) use ($date) {
            $q->whereDate('dates', $date)->with('attendanceCode');
        }]);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Apply status filters if needed, similar to index
        if ($request->filled('status')) {
             $status = $request->status;
             $isCode = \App\Models\AttendanceCode::where('name', $status)->exists();
             
             if ($isCode) {
                 $query->whereHas('attendances', function($q) use ($date, $status) {
                     $q->whereDate('dates', $date)->whereHas('attendanceCode', fn($sq) => $sq->where('name', $status));
                 });
             } elseif ($status === 'Alpha' || $status === 'Belum Absensi' || $status === 'Belum Tersedia') {
                  $query->whereDoesntHave('attendances', function($q) use ($date) {
                      $q->whereDate('dates', $date);
                  });
             }
        }

        $students = $query->orderBy('name')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentDailyExport($students, $date), 'Laporan_Harian_Siswa_' . $date . '.xlsx');
    }

    public function exportTeacherDaily(Request $request)
    {
        $date = $request->get('start_date', Carbon::today()->toDateString());

        $query = \App\Models\Teacher::with(['attendances' => function($q) use ($date) {
            $q->whereDate('dates', $date)->with('attendanceCode');
        }]);
        
        if ($request->filled('status')) {
             $status = $request->status;
             $isCode = \App\Models\AttendanceCode::where('name', $status)->exists();
             if ($isCode) {
                 $query->whereHas('attendances', function($q) use ($date, $status) {
                     $q->whereDate('dates', $date)->whereHas('attendanceCode', fn($sq) => $sq->where('name', $status));
                 });
             } elseif ($status === 'Alpha' || $status === 'Belum Absensi') {
                  $query->whereDoesntHave('attendances', function($q) use ($date) {
                      $q->whereDate('dates', $date);
                  });
             }
        }

        $teachers = $query->orderBy('name')->get();

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TeacherDailyExport($teachers, $date), 'Laporan_Harian_Guru_' . $date . '.xlsx');
    }
}
