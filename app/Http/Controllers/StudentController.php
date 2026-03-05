<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use App\Models\Level;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $levels = Level::all();
        $classes = SchoolClass::all();

        $query = Student::with('classRoom.level');

        if ($request->filled('level_id')) {
            $query->whereHas('classRoom', function ($q) use ($request) {
                $q->where('level_id', $request->level_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        // Search handled by DataTables client-side if we load all. 
        // However, if we filter by class/level server-side, we get a smaller subset.
        // We can keep server-side search optionally or remove it.
        // Let's remove server-side search to rely on DataTables for the loaded subset.
        
        $students = $query->latest()->get(); // Get ALL (filtered) for client-side datatable

        return view('master.students.index', compact('students', 'levels', 'classes'));
    }

    public function create()
    {
        $classes = SchoolClass::all();
        return view('master.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nis' => 'required|unique:students',
            'class_id' => 'required',
            'gender' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();
        $data['unique_code'] = (string) \Illuminate\Support\Str::uuid();

        if ($request->hasFile('photo')) {
            $imageName = time().'.'.$request->photo->extension();
            $request->photo->storeAs('public/photo/students', $imageName);
            $data['photo'] = $imageName;
        }

        Student::create($data);

        return redirect()->route('students.index')->with('success', 'Siswa berhasil ditambahkan');
    }

    public function show(Student $student)
    {
        $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
        $qrCodeRaw = QrCode::format('png')->size(360)->generate($student->unique_code);
        $manager = new ImageManager(new Driver());

        $qrResource = @imagecreatefromstring($qrCodeRaw);
        if (!$qrResource) {
            throw new \Exception('Native GD imagecreatefromstring failed to read QR content.');
        }
        
        $qrImage = $manager->read($qrResource);

        if ($logoPath && file_exists($logoPath)) {
            $logoSize = 80; // Ukuran logo di tengah (diperkecil agar ramah barcode reader)
            $padding = 2; // Padding putih keliling logo
            $circleDiameter = $logoSize + ($padding * 2);
            
            // Buat canvas putih transparan (kosong/alpha)
            $whiteCircleCanvas = $manager->create($circleDiameter, $circleDiameter);
            
            // Gambar lingkaran putih murni/solid sebesar kanvas
            $whiteCircleCanvas->drawCircle($circleDiameter / 2, $circleDiameter / 2, function ($circle) use ($circleDiameter) {
                $circle->radius(intval($circleDiameter / 2));
                $circle->background('ffffff'); // Warna latar di dalam radius
            });
            
            // Baca logo asli, resize agar muat
            $logoImg = $manager->read($logoPath);
            $logoImg->resize($logoSize, $logoSize);
            
            // Taruh logo di center lingkaran putih
            $whiteCircleCanvas->place($logoImg, 'center');
            
            // Taruh kotak putih (berisi logo) ke center QR Code
            $qrImage->place($whiteCircleCanvas, 'center');
        }

        $base64Code = $qrImage->toPng()->toDataUri();

        $qrcode = '<img src="'. $base64Code . '" alt="QR Code">';
        
        $idCardPath = 'id_cards/student_' . $student->id . '.png';
        $hasIdCard = \Illuminate\Support\Facades\Storage::disk('public')->exists($idCardPath);
        $idCardUrl = $hasIdCard ? asset('storage/' . $idCardPath) : null;

        return view('master.students.show', compact('student', 'qrcode', 'hasIdCard', 'idCardUrl'));
    }

    public function generateCard(Student $student)
    {
        $service = new \App\Services\IdCardService();
        $result = $service->generateStudentCard($student);

        if ($result['success']) {
            return redirect()->route('students.show', $student->id)->with('success', 'ID Card berhasil digenerate');
        } else {
            return redirect()->route('students.show', $student->id)->with('error', 'Gagal generate ID Card: ' . $result['message']);
        }
    }

    public function edit(Student $student)
    {
        $classes = SchoolClass::all();
        return view('master.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required',
            'nis' => 'required|unique:students,nis,' . $student->id,
            'class_id' => 'required',
            'gender' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
             // Delete old photo
            if ($student->photo && \Illuminate\Support\Facades\Storage::exists('public/photo/students/' . $student->photo)) {
                \Illuminate\Support\Facades\Storage::delete('public/photo/students/' . $student->photo);
            }

            $imageName = time().'.'.$request->photo->extension();
            $request->photo->storeAs('public/photo/students', $imageName);
            $data['photo'] = $imageName;
        }

        $student->update($data);

        return redirect()->route('students.index')->with('success', 'Data siswa berhasil diperbarui');
    }

    public function import()
    {
        return view('master.students.import');
    }

    public function downloadTemplate()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentTemplateExport, 'template_import_siswa.xlsx');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\StudentImport, $request->file('file'));
            });

            return redirect()->route('students.index')->with('success', 'Data siswa berhasil diimport secara massal');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(', ', $failure->errors());
            }
            
            return redirect()->back()->with('import_errors', $errorMessages);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function destroy(Student $student)
    {
        if ($student->photo && \Illuminate\Support\Facades\Storage::exists('public/photo/students/' . $student->photo)) {
            \Illuminate\Support\Facades\Storage::delete('public/photo/students/' . $student->photo);
        }
        
        $student->delete();
        return redirect()->route('students.index')->with('success', 'Siswa berhasil dihapus');
    }
}
