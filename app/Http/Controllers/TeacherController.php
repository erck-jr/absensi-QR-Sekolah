<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::latest()->get();
        return view('master.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('master.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'nuptk' => 'required|unique:teachers',
            'gender' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();
        $data['unique_code'] = (string) \Illuminate\Support\Str::uuid();

        if ($request->hasFile('photo')) {
            $imageName = time().'.'.$request->photo->extension();
            $request->photo->storeAs('public/photo/teachers', $imageName);
            $data['photo'] = $imageName;
        }

        Teacher::create($data);

        return redirect()->route('teachers.index')->with('success', 'Guru berhasil ditambahkan');
    }

    public function show(Teacher $teacher)
    {
        $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
        $qrCodeRaw = QrCode::format('png')->size(360)->generate($teacher->unique_code);
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
        
        $frontPath = 'id_cards/teacher_' . $teacher->id . '_front.png';
        $backPath = 'id_cards/teacher_' . $teacher->id . '_back.png';
        
        $hasFront = \Illuminate\Support\Facades\Storage::disk('public')->exists($frontPath);
        $hasBack = \Illuminate\Support\Facades\Storage::disk('public')->exists($backPath);
        
        $frontUrl = $hasFront ? asset('storage/' . $frontPath) : null;
        $backUrl = $hasBack ? asset('storage/' . $backPath) : null;

        return view('master.teachers.show', compact('teacher', 'qrcode', 'hasFront', 'hasBack', 'frontUrl', 'backUrl'));
    }

    public function generateCard(Teacher $teacher)
    {
        $service = new \App\Services\IdCardService();
        $result = $service->generateTeacherCard($teacher);

        if ($result['success']) {
            return redirect()->route('teachers.show', $teacher->id)->with('success', 'ID Card berhasil digenerate');
        } else {
            return redirect()->route('teachers.show', $teacher->id)->with('error', 'Gagal generate ID Card: ' . $result['message']);
        }
    }

    public function edit(Teacher $teacher)
    {
        return view('master.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required',
            'nuptk' => 'required|unique:teachers,nuptk,' . $teacher->id,
            'gender' => 'required',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('photo')) {
             // Delete old photo
            if ($teacher->photo && \Illuminate\Support\Facades\Storage::exists('public/photo/teachers/' . $teacher->photo)) {
                \Illuminate\Support\Facades\Storage::delete('public/photo/teachers/' . $teacher->photo);
            }

            $imageName = time().'.'.$request->photo->extension();
            $request->photo->storeAs('public/photo/teachers', $imageName);
            $data['photo'] = $imageName;
        }

        $teacher->update($data);

        return redirect()->route('teachers.index')->with('success', 'Data guru berhasil diperbarui');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->photo && \Illuminate\Support\Facades\Storage::exists('public/photo/teachers/' . $teacher->photo)) {
            \Illuminate\Support\Facades\Storage::delete('public/photo/teachers/' . $teacher->photo);
        }

        $teacher->delete();
        return redirect()->route('teachers.index')->with('success', 'Guru berhasil dihapus');
    }
}
