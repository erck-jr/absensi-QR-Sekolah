<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\IdCardService;
use Illuminate\Http\Request;

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
        $students = $query->pluck('id'); // Return IDs only for client-side iteration
        return response()->json($students);
    }

    public function getTeachers()
    {
        $teachers = Teacher::pluck('id');
        return response()->json($teachers);
    }

    public function generateStudent(Request $request)
    {
        $request->validate(['id' => 'required|exists:students,id']);
        $student = Student::with('classRoom')->find($request->id);
        
        $result = $this->idCardService->generateStudentCard($student);
        
        return response()->json($result);
    }

    public function generateTeacher(Request $request)
    {
        $request->validate(['id' => 'required|exists:teachers,id']);
        $teacher = Teacher::find($request->id);
        
        $result = $this->idCardService->generateTeacherCard($teacher);
        
        return response()->json($result);
    }

    public function downloadZip(Request $request)
    {
        $type = $request->type; // 'student' or 'teacher'
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

        if ($type === 'student') {
            $query = Student::query();
            if ($classId) {
                $query->where('class_id', $classId);
            }
            $items = $query->get();
            $prefix = 'student_';
        } else {
            $items = Teacher::all();
            $prefix = 'teacher_';
        }

        $addedCount = 0;
        foreach ($items as $item) {
            $fileName = $prefix . $item->id . '.png';
            if ($type === 'teacher') {
                // Teacher has front and back usually, or just student-like? 
                // Service saves teacher_{id}_front.png and back.png
                // Let's check service again.
                $fileNameFront = "teacher_{$item->id}_front.png";
                $fileNameBack = "teacher_{$item->id}_back.png";
                
                $pathFront = storage_path("app/public/id_cards/{$fileNameFront}");
                $pathBack = storage_path("app/public/id_cards/{$fileNameBack}");

                if (file_exists($pathFront)) {
                    $zip->addFile($pathFront, $fileNameFront);
                    $addedCount++;
                }
                if (file_exists($pathBack)) {
                    $zip->addFile($pathBack, $fileNameBack);
                    $addedCount++;
                }
            } else {
                $path = storage_path("app/public/id_cards/{$fileName}");
                if (file_exists($path)) {
                    $zip->addFile($path, $fileName);
                    $addedCount++;
                }
            }
        }

        $zip->close();

        if ($addedCount === 0) {
            @unlink($zipPath);
            return back()->with('error', 'Tidak ada file ID Card yang ditemukan untuk diunduh. Silakan generate terlebih dahulu.');
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
