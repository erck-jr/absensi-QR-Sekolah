<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\CardTemplate;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class IdCardService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function generateStudentCard($student)
    {
        Log::info('Starting Student Card Generation (Intervention)', ['student_id' => $student->id]);

        // 1. Get Template
        $templateName = CardTemplate::where('key', 'student_front')->value('file_name');
        
        if (!$templateName) {
            return ['success' => false, 'message' => 'Template name not found in database'];
        }

        $templatePath = public_path('templates_card/' . $templateName);

        if (!file_exists($templatePath)) {
            return ['success' => false, 'message' => 'Template file not found at: ' . $templatePath];
        }

        try {
            // 2. Load Image
            Log::info('Reading template from: ' . $templatePath);
            $image = $this->manager->read($templatePath);
            Log::info('Template read successfully.');

            //Get WIDTH, HEIGHT
            $width = $image->width();
            $height = $image->height();

            // 3. Add Text
            $fontPath = public_path('fonts/Lato-Bold.ttf');
            $fontPath2 = public_path('fonts/Lato-Regular.ttf');

            // Name
            $fontSize = 24;
            $maxWidth = $width - 100; // Total Lebar Canvas - Margin Kiri 50px - Margin Kanan 50px
            $formattedName = $this->formatShortName($student->name, $maxWidth, $fontPath, $fontSize);

            $image->text(strtoupper($formattedName), $width / 2, $height - 190, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize); // Adjusted size for better visibility
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom'); 
            });

            // NIS
            $image->text("NIS: " . $student->nis, $width/2, $height - 115, function ($font) use ($fontPath2) {
                $font->file($fontPath2);
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // 3. Add QR Code
            $qrContent = QrCode::format('png')->size(360)->generate($student->unique_code);

            // Explicitly try GD native create first to identify issues
            $qrResource = @imagecreatefromstring($qrContent);
            if (!$qrResource) {
                 throw new \Exception('Native GD imagecreatefromstring failed to read QR content.');
            }
            
            $qrImage = $this->manager->read($qrResource);

            // Terapkan Logo App dengan Background Putih Bulat
            $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
            if ($logoPath && file_exists($logoPath)) {
                $logoSize = 80; // Ukuran logo di tengah (diperkecil agar ramah barcode reader)
                $padding = 2; // Padding putih keliling logo
                $circleDiameter = $logoSize + ($padding * 2);
                
                // Buat canvas putih transparan (kosong/alpha)
                $whiteCircleCanvas = $this->manager->create($circleDiameter, $circleDiameter);
                
                // Gambar lingkaran putih murni/solid sebesar kanvas
                $whiteCircleCanvas->drawCircle($circleDiameter / 2, $circleDiameter / 2, function ($circle) use ($circleDiameter) {
                    $circle->radius(intval($circleDiameter / 2));
                    $circle->background('ffffff'); // Warna latar di dalam radius
                });
                
                // Baca logo asli, resize agar muat
                $logoImg = $this->manager->read($logoPath);
                $logoImg->resize($logoSize, $logoSize);
                
                // Taruh logo di center lingkaran putih
                $whiteCircleCanvas->place($logoImg, 'center');
                
                // Taruh kotak putih (berisi logo) ke center QR Code
                $qrImage->place($whiteCircleCanvas, 'center');
            }

            // Place customized QR Code at bottom-center with center padding based on template
            $image->place($qrImage, 'bottom-center', 0, ($height/3) - 70);

            // 5. Save
            $fileName = 'student_' . $student->id . '.png';
            
            if (!Storage::disk('public')->exists('id_cards')) {
                Storage::disk('public')->makeDirectory('id_cards');
            }

            $path = Storage::disk('public')->path('id_cards/' . $fileName);
            Log::info('Saving Student ID Card', ['path' => $path]);

            $image->save($path);

            Log::info('Student ID Card Saved Successfully');
            return ['success' => true, 'url' => asset('storage/id_cards/' . $fileName)];

        } catch (\Exception $e) {
            Log::error('Failed to generate Student ID Card', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to generate image: ' . $e->getMessage()];
        }
    }

    public function generateTeacherCard($teacher)
    {
        Log::info('Starting Teacher Card Generation (Intervention)', ['teacher_id' => $teacher->id]);

        // --- FRONT SIDE GENERATION ---
        
        // 1. Get Front Template
        $templateNameFront = CardTemplate::where('key', 'teacher_front')->value('file_name');
        if (!$templateNameFront) {
            return ['success' => false, 'message' => 'Template front name not found'];
        }
        $templatePathFront = public_path('templates_card/' . $templateNameFront);
        if (!file_exists($templatePathFront)) {
            return ['success' => false, 'message' => 'Template front file not found'];
        }

        try {
            // Load Front Template
            $imageFront = $this->manager->read($templatePathFront);
            $width = $imageFront->width();
            $height = $imageFront->height();
            $fontPath = public_path('fonts/Lato-Bold.ttf');

            // 2. Add Photo (Overlay)
            // Fix: Use 'public' disk explicitly and correct path
            if ($teacher->photo && Storage::disk('public')->exists('photo/teachers/' . $teacher->photo)) {
                $photoPath = Storage::disk('public')->path('photo/teachers/' . $teacher->photo);
                $photo = $this->manager->read($photoPath);
                
                // Resize photo to square and reasonable size (e.g. 300x300 or based on template design)
                // Assuming a standard design, let's make it 300x300 fit
                $photo->resize(400, 400);
                
                // Place photo. Position needs to be determined. 
                // Suggestion: Top-Center or specifically defined. 
                // For now, let's place it at top-center with some padding.
                $imageFront->place($photo, 'top-center', -2, 458);
            }

            // 3. Add Text (Front)
            // Name
            $fontSize = 24;
            $maxWidth = $width - 100; // Total Lebar Canvas - Margin Kiri 50px - Margin Kanan 50px
            $formattedName = $this->formatShortName($teacher->name, $maxWidth, $fontPath, $fontSize);

            $imageFront->text(strtoupper($formattedName), $width / 2, $height - 180, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });
            
            // NUPTK
            $imageFront->text("NUPTK: " . $teacher->nuptk, $width/2, $height - 110, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // Save Front
            $fileNameFront = 'teacher_' . $teacher->id . '_front.png';
            if (!Storage::disk('public')->exists('id_cards')) {
                Storage::disk('public')->makeDirectory('id_cards');
            }
            $pathFront = Storage::disk('public')->path('id_cards/' . $fileNameFront);
            $imageFront->save($pathFront);


            // --- BACK SIDE GENERATION ---

            // 1. Get Back Template (or reuse front if generic, but usually specific)
            // If teacher_back doesn't exist, maybe use a default or blank?
            // Checking if key exists first.
            $templateNameBack = CardTemplate::where('key', 'teacher_back')->value('file_name');
            
            // If no back template, create a blank one or use front (fallback) - ideally should be a separate white canvas
            if ($templateNameBack && file_exists(public_path('templates_card/' . $templateNameBack))) {
                $imageBack = $this->manager->read(public_path('templates_card/' . $templateNameBack));
            } else {
                // Fallback: Create white background same size as front
                // Intervention 3 create logic
                $imageBack = $this->manager->create($width, $height)->fill('ffffff');
            }

            // 2. Add QR Code to Back
            $qrContent = QrCode::format('png')->size(360)->generate($teacher->unique_code);
            
            // Explicitly try GD native create
            $qrResource = @imagecreatefromstring($qrContent);
            if (!$qrResource) {
                 throw new \Exception('Native GD imagecreatefromstring failed to read QR content.');
            }
            $qrImage = $this->manager->read($qrResource);

            // Terapkan Logo App dengan Background Putih Bulat
            $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
            if ($logoPath && file_exists($logoPath)) {
                $logoSize = 80; // Ukuran logo di tengah (diperkecil agar ramah barcode reader)
                $padding = 2; // Padding putih keliling logo
                $circleDiameter = $logoSize + ($padding * 2);
                
                // Buat canvas putih transparan (kosong/alpha)
                $whiteCircleCanvas = $this->manager->create($circleDiameter, $circleDiameter);
                
                // Gambar lingkaran putih murni/solid sebesar kanvas
                $whiteCircleCanvas->drawCircle($circleDiameter / 2, $circleDiameter / 2, function ($circle) use ($circleDiameter) {
                    $circle->radius(intval($circleDiameter / 2));
                    $circle->background('ffffff'); // Warna latar di dalam radius
                });
                
                $logoImg = $this->manager->read($logoPath);
                $logoImg->resize($logoSize, $logoSize);
                
                $whiteCircleCanvas->place($logoImg, 'center');
                $qrImage->place($whiteCircleCanvas, 'center');
            }

            // Place QR Code at Center
            $imageBack->place($qrImage, 'center');

            // Save Back
            $fileNameBack = 'teacher_' . $teacher->id . '_back.png';
            $pathBack = Storage::disk('public')->path('id_cards/' . $fileNameBack);
            $imageBack->save($pathBack);


            Log::info('Teacher ID Card (Front/Back) Saved Successfully');
            
            return [
                'success' => true, 
                'front_url' => asset('storage/id_cards/' . $fileNameFront),
                'back_url' => asset('storage/id_cards/' . $fileNameBack),
                // Keep 'url' for backward compatibility if needed, pointing to front
                'url' => asset('storage/id_cards/' . $fileNameFront) 
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Teacher ID Card', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to generate image: ' . $e->getMessage()];
        }
    }

    /**
     * Helper to shorten name based on text width and container limit.
     * 
     * @param string $name
     * @param int $widthLimit The max allowed width in pixels (e.g. Card Width - 100px)
     * @param string $fontFilePath The true type font path
     * @param int $fontSize The font size used
     * @return string
     */
    private function formatShortName($name, $widthLimit, $fontFilePath, $fontSize)
    {
        $name = trim($name);
        
        // Cek lebar rentetan teks saat ini menggunakan imagettfbbox
        // referensi array bounding box: 0=kiri-bawah(x), 4=kanan-atas(x)
        $box = imagettfbbox($fontSize, 0, $fontFilePath, strtoupper($name));
        $textWidth = abs($box[4] - $box[0]);

        // Jika lebar teks masih aman di bawah limit margin (100px di luar), JANGAN dipotong.
        if ($textWidth <= $widthLimit) {
            return $name;
        }

        $words = explode(' ', $name);
        $count = count($words);

        // Abaikan pemotongan jika cuma 1 kata
        if ($count <= 1) {
            return $name;
        }

        if ($count == 2) {
            // Singkat kata belakang: Ahmad Santoso -> Ahmad S.
            $words[1] = mb_substr($words[1], 0, 1) . '.';
            return implode(' ', $words);
        }

        // Jika >= 3 kata, singkat semua kata *tengah*
        // Contoh: Ahmad Zain Nur Santoso -> Ahmad Z. N. Santoso
        $firstWord = $words[0];
        $lastWord = $words[$count - 1];
        $middleInitials = [];

        for ($i = 1; $i < $count - 1; $i++) {
            $middleInitials[] = mb_substr($words[$i], 0, 1) . '.';
        }

        return $firstWord . ' ' . implode(' ', $middleInitials) . ' ' . $lastWord;
    }
}
