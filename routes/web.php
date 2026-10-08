<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\CardGeneratorController;
use App\Http\Controllers\CardTemplateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicAttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaGatewayController;
use App\Http\Controllers\WaLogController;
use Illuminate\Support\Facades\Route;

// --- RUTE PUBLIK (Dapat diakses tanpa login) ---
Route::get('/', function () {
    return view('welcome');
})->name('welcome'); //[cite: 3]

Route::get('/cek-kehadiran', [PublicAttendanceController::class, 'index'])->name('public.attendance.index'); //[cite: 3]
Route::post('/cek-kehadiran', [PublicAttendanceController::class, 'check'])->name('public.attendance.check'); //[cite: 3]

// External Cron Route
Route::get('/cron/process-queue', [WaGatewayController::class, 'cronProcessQueue']); //[cite: 3]


// --- RUTE TERPROTEKSI (Wajib Login) ---
Route::middleware(['auth'])->group(function () { //[cite: 3]

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('verified')->name('dashboard'); //[cite: 3]

    // Operator / Public Authenticated Routes
    Route::resource('scanner', ScannerController::class)->only(['index', 'store']); //[cite: 3]
    Route::resource('guests', GuestController::class)->only(['index', 'store', 'update']); //[cite: 3]

    // Students Import & Custom Routes (Harus di atas resource)
    Route::get('students/import', [StudentController::class, 'import'])->name('students.import'); //[cite: 3]
    Route::post('students/import', [StudentController::class, 'processImport'])->name('students.import.process'); //[cite: 3]
    Route::get('students/template', [StudentController::class, 'downloadTemplate'])->name('students.import.template'); //[cite: 3]
    Route::post('students/{student}/generate-card', [StudentController::class, 'generateCard'])->name('students.generate-card'); //[cite: 3]
    Route::resource('students', StudentController::class); //[cite: 3]

    // Teachers Custom Routes & Resource
    Route::post('teachers/{teacher}/generate-card', [TeacherController::class, 'generateCard'])->name('teachers.generate-card'); //[cite: 3]
    Route::resource('teachers', TeacherController::class); //[cite: 3]

    // Master Data Lainya
    Route::resource('levels', LevelController::class); //[cite: 3]
    Route::resource('classes', SchoolClassController::class); //[cite: 3]
    Route::post('shifts/{shift}/toggle', [ShiftController::class, 'toggle'])->name('shifts.toggle'); //[cite: 3]
    Route::resource('shifts', ShiftController::class); //[cite: 3]
    Route::resource('holidays', HolidayController::class); //[cite: 3]

    // Reports
    Route::get('reports/students', [ReportController::class, 'studentIndex'])->name('reports.students'); //[cite: 3]
    Route::post('/laporan-siswa/update', [ReportController::class, 'updateStudentAttendance'])->name('reports.students.update'); //[cite: 3]
    Route::get('/laporan-siswa/export', [ReportController::class, 'exportStudentMonthly'])->name('reports.students.export'); //[cite: 3]
    Route::get('/laporan-siswa/export-daily', [ReportController::class, 'exportStudentDaily'])->name('reports.students.export-daily'); //[cite: 3]
    
    Route::get('reports/teachers', [ReportController::class, 'teacherIndex'])->name('reports.teachers'); //[cite: 3]
    Route::post('/laporan-guru/update', [ReportController::class, 'updateTeacherAttendance'])->name('reports.teachers.update'); //[cite: 3]
    Route::get('/laporan-guru/export', [ReportController::class, 'exportTeacherMonthly'])->name('reports.teachers.export'); //[cite: 3]
    Route::get('/laporan-guru/export-daily', [ReportController::class, 'exportTeacherDaily'])->name('reports.teachers.export-daily'); //[cite: 3]

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit'); //[cite: 3]
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); //[cite: 3]
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy'); //[cite: 3]


    // --- RUTE KHUSUS ADMIN ---
    Route::middleware('admin')->group(function () { //[cite: 3]
        Route::resource('settings', SettingController::class)->only(['index']); //[cite: 3]
        Route::post('settings/update', [SettingController::class, 'update'])->name('settings.update'); //[cite: 3]
        Route::resource('users', UserController::class); //[cite: 3]
        Route::resource('message-templates', MessageTemplateController::class)->only(['index', 'update']); //[cite: 3]
        Route::resource('card-templates', CardTemplateController::class)->only(['index', 'update']); //[cite: 3]
        
        // WA Gateway
        Route::post('wagateways/settings', [WaGatewayController::class, 'updateSettings'])->name('wagateways.settings.update'); //[cite: 3]
        Route::post('wagateways/process-queue', [WaGatewayController::class, 'processQueue'])->name('wagateways.process_queue'); //[cite: 3]
        Route::post('wagateways/retry-queue', [WaGatewayController::class, 'RetryQueue'])->name('wagateways.retry_queue'); //[cite: 3]
        Route::resource('wagateways', WaGatewayController::class); //[cite: 3]
        
        // WA Logs
        Route::get('walogs', [WaLogController::class, 'index'])->name('walogs.index'); //[cite: 3]
        Route::get('walogs/export', [WaLogController::class, 'export'])->name('walogs.export'); //[cite: 3]
        Route::post('walogs/clear', [WaLogController::class, 'clear'])->name('walogs.clear'); //[cite: 3]

        // Academic Year
        Route::get('academic-year', [AcademicYearController::class, 'index'])->name('academic.year.index'); //[cite: 3]
        Route::post('academic-year/promote', [AcademicYearController::class, 'promote'])->name('academic.year.promote'); //[cite: 3]
        Route::post('academic-year/cleanup', [AcademicYearController::class, 'cleanup'])->name('academic.year.cleanup'); //[cite: 3]
        Route::get('academic-year/download/{filename}', [AcademicYearController::class, 'downloadBackup'])->name('academic.year.download'); //[cite: 3]
        Route::delete('academic-year/delete-backup/{filename}', [AcademicYearController::class, 'deleteBackup'])->name('academic.year.delete-backup'); //[cite: 3]
        Route::post('academic-year/restore-guests/{filename}', [AcademicYearController::class, 'restoreGuests'])->name('academic.year.restore-guests'); //[cite: 3]

        // Generator
        Route::get('generator', [CardGeneratorController::class, 'index'])->name('generator.index'); //[cite: 3]
        Route::get('generator/get-students', [CardGeneratorController::class, 'getStudents'])->name('generator.get-students'); //[cite: 3]
        Route::get('generator/get-teachers', [CardGeneratorController::class, 'getTeachers'])->name('generator.get-teachers'); //[cite: 3]
        Route::post('generator/student', [CardGeneratorController::class, 'generateStudent'])->name('generator.student'); //[cite: 3]
        Route::post('generator/teacher', [CardGeneratorController::class, 'generateTeacher'])->name('generator.teacher'); //[cite: 3]
        Route::post('generator/mass-student', [CardGeneratorController::class, 'massGenerateStudent'])->name('generator.mass-student'); //[cite: 3]
        Route::post('generator/mass-teacher', [CardGeneratorController::class, 'massGenerateTeacher'])->name('generator.mass-teacher'); //[cite: 3]
        Route::get('generator/download-zip', [CardGeneratorController::class, 'downloadZip'])->name('generator.download-zip'); //[cite: 3]
    });
});

require __DIR__.'/auth.php'; //[cite: 3]