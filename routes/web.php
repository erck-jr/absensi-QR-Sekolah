<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\CardTemplateController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\WaLogController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Public Attendance Check
Route::get('/cek-kehadiran', [\App\Http\Controllers\PublicAttendanceController::class, 'index'])->name('public.attendance.index');
Route::post('/cek-kehadiran', [\App\Http\Controllers\PublicAttendanceController::class, 'check'])->name('public.attendance.check');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    // Public/Operator Routes
    Route::resource('scanner', ScannerController::class)->only(['index', 'store']);
    // Students Import
    Route::get('students/import', [StudentController::class, 'import'])->name('students.import');
    Route::post('students/import', [StudentController::class, 'processImport'])->name('students.import.process');
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
    Route::resource('guests', GuestController::class)->only(['index', 'store', 'update']);
    
    // Reports
    Route::get('reports/students', [ReportController::class, 'studentIndex'])->name('reports.students');
    Route::post('/laporan-siswa/update', [ReportController::class, 'updateStudentAttendance'])->name('reports.students.update');
    Route::get('/laporan-siswa/export', [ReportController::class, 'exportStudentMonthly'])->name('reports.students.export');
    Route::get('/laporan-siswa/export-daily', [ReportController::class, 'exportStudentDaily'])->name('reports.students.export-daily');
    
    Route::get('reports/teachers', [ReportController::class, 'teacherIndex'])->name('reports.teachers');
    Route::get('/laporan-guru/export', [ReportController::class, 'exportTeacherMonthly'])->name('reports.teachers.export');
    Route::get('/laporan-guru/export-daily', [ReportController::class, 'exportTeacherDaily'])->name('reports.teachers.export-daily');

    // Master Data (Allowed for Operators, except Generator)
    Route::post('students/{student}/generate-card', [\App\Http\Controllers\StudentController::class, 'generateCard'])->name('students.generate-card');
    Route::resource('students', \App\Http\Controllers\StudentController::class);
    
    Route::post('teachers/{teacher}/generate-card', [\App\Http\Controllers\TeacherController::class, 'generateCard'])->name('teachers.generate-card');
    Route::resource('teachers', \App\Http\Controllers\TeacherController::class);
    Route::resource('levels', \App\Http\Controllers\LevelController::class);
    Route::resource('classes', \App\Http\Controllers\SchoolClassController::class);
    Route::post('shifts/{shift}/toggle', [\App\Http\Controllers\ShiftController::class, 'toggle'])->name('shifts.toggle');
    Route::resource('shifts', \App\Http\Controllers\ShiftController::class);
    Route::resource('holidays', HolidayController::class); // Data Libur

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin Only Routes
    Route::middleware('admin')->group(function () {
        Route::post('/laporan-guru/update', [ReportController::class, 'updateTeacherAttendance'])->name('reports.teachers.update');
        
        Route::resource('settings', SettingController::class)->only(['index']);
        Route::post('settings/update', [SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', \App\Http\Controllers\UserController::class);
        Route::resource('message-templates', MessageTemplateController::class)->only(['index', 'update']);
        Route::resource('card-templates', CardTemplateController::class)->only(['index', 'update']);
        Route::resource('wagateways', \App\Http\Controllers\WaGatewayController::class);
        Route::get('walogs', [WaLogController::class, 'index'])->name('walogs.index');

        // ID Card Generator
        Route::get('generator', [\App\Http\Controllers\CardGeneratorController::class, 'index'])->name('generator.index');
        Route::get('generator/get-students', [\App\Http\Controllers\CardGeneratorController::class, 'getStudents'])->name('generator.get-students');
        Route::get('generator/get-teachers', [\App\Http\Controllers\CardGeneratorController::class, 'getTeachers'])->name('generator.get-teachers');
        Route::post('generator/student', [\App\Http\Controllers\CardGeneratorController::class, 'generateStudent'])->name('generator.student');
        Route::post('generator/teacher', [\App\Http\Controllers\CardGeneratorController::class, 'generateTeacher'])->name('generator.teacher');
        Route::get('generator/download-zip', [\App\Http\Controllers\CardGeneratorController::class, 'downloadZip'])->name('generator.download-zip');
    });

require __DIR__.'/auth.php';
