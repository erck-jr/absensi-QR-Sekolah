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

    /**
     * Generate ID Card untuk Siswa.
     *
     * @param  \App\Models\Student  $student
     * @param  bool  $isOverwrite  true = selalu generate ulang (single/profile page),
     *                             false = mode cached (massal, skip jika file sudah ada)
     * @return array
     */
    public function generateStudentCard($student, bool $isOverwrite = true): array
    {
        Log::info('Starting Student Card Generation', [
            'student_id'   => $student->id,
            'is_overwrite' => $isOverwrite,
        ]);

        // --- Tentukan path output ---
        $className   = $this->sanitizeFileName($student->classRoom->name ?? 'tanpa_kelas');
        $baseName    = $this->sanitizeFileName($student->name) . '_' . $student->nis;
        $fileName    = $baseName . '.png';
        $subDir      = 'id_cards/students/' . $className;
        $storagePath = $subDir . '/' . $fileName;

        // Mode cached: jika file sudah ada, langsung kembalikan URL tanpa generate ulang
        if (!$isOverwrite && Storage::disk('public')->exists($storagePath)) {
            Log::info('Student card already cached, skipping generation.', ['path' => $storagePath]);
            return [
                'success' => true,
                'cached'  => true,
                'url'     => asset('storage/' . $storagePath),
            ];
        }

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

            // Get WIDTH, HEIGHT
            $width  = $image->width();
            $height = $image->height();

            // 3. Add Text
            $fontPath  = public_path('fonts/Lato-Bold.ttf');
            $fontPath2 = public_path('fonts/Lato-Regular.ttf');

            // Name
            $fontSize      = 24;
            $maxWidth      = $width - 100;
            $formattedName = $this->formatShortName($student->name, $maxWidth, $fontPath, $fontSize);

            $image->text(strtoupper($formattedName), $width / 2, $height - 190, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // NIS
            $image->text("NIS: " . $student->nis, $width / 2, $height - 115, function ($font) use ($fontPath2) {
                $font->file($fontPath2);
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // 4. Add QR Code
            $qrContent  = QrCode::format('png')->size(360)->generate($student->unique_code);
            $qrResource = @imagecreatefromstring($qrContent);
            if (!$qrResource) {
                throw new \Exception('Native GD imagecreatefromstring failed to read QR content.');
            }
            $qrImage = $this->manager->read($qrResource);

            // Terapkan Logo App dengan Background Putih Bulat
            $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
            if ($logoPath && file_exists($logoPath)) {
                $logoSize      = 80;
                $padding       = 2;
                $circleDiameter = $logoSize + ($padding * 2);

                $whiteCircleCanvas = $this->manager->create($circleDiameter, $circleDiameter);
                $whiteCircleCanvas->drawCircle($circleDiameter / 2, $circleDiameter / 2, function ($circle) use ($circleDiameter) {
                    $circle->radius(intval($circleDiameter / 2));
                    $circle->background('ffffff');
                });

                $logoImg = $this->manager->read($logoPath);
                $logoImg->resize($logoSize, $logoSize);

                $whiteCircleCanvas->place($logoImg, 'center');
                $qrImage->place($whiteCircleCanvas, 'center');
            }

            $image->place($qrImage, 'bottom-center', 0, ($height / 3) - 70);

            // 5. Pastikan folder ada, lalu simpan
            if (!Storage::disk('public')->exists($subDir)) {
                Storage::disk('public')->makeDirectory($subDir);
            }

            $physicalPath = Storage::disk('public')->path($storagePath);
            Log::info('Saving Student ID Card', ['path' => $physicalPath]);
            $image->save($physicalPath);

            Log::info('Student ID Card Saved Successfully');
            return [
                'success' => true,
                'cached'  => false,
                'url'     => asset('storage/' . $storagePath),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Student ID Card', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to generate image: ' . $e->getMessage()];
        }
    }

    /**
     * Generate ID Card untuk Guru (Front + Back).
     *
     * @param  \App\Models\Teacher  $teacher
     * @param  bool  $isOverwrite  true = selalu generate ulang (single/profile page),
     *                             false = mode cached (massal, skip jika file sudah ada)
     * @return array
     */
    public function generateTeacherCard($teacher, bool $isOverwrite = true): array
    {
        Log::info('Starting Teacher Card Generation', [
            'teacher_id'   => $teacher->id,
            'is_overwrite' => $isOverwrite,
        ]);

        // --- Tentukan path output ---
        $baseName        = $this->sanitizeFileName($teacher->name) . '_' . $teacher->nuptk;
        $fileNameFront   = $baseName . '_front.png';
        $fileNameBack    = $baseName . '_back.png';
        $subDir          = 'id_cards/teachers';
        $storagePathFront = $subDir . '/' . $fileNameFront;
        $storagePathBack  = $subDir . '/' . $fileNameBack;

        // Mode cached: jika file front sudah ada, anggap sudah digenerate
        if (!$isOverwrite && Storage::disk('public')->exists($storagePathFront)) {
            Log::info('Teacher card already cached, skipping generation.', ['path' => $storagePathFront]);
            return [
                'success'   => true,
                'cached'    => true,
                'front_url' => asset('storage/' . $storagePathFront),
                'back_url'  => asset('storage/' . $storagePathBack),
                'url'       => asset('storage/' . $storagePathFront),
            ];
        }

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
            $width      = $imageFront->width();
            $height     = $imageFront->height();
            $fontPath   = public_path('fonts/Lato-Bold.ttf');

            // 2. Add Photo (Overlay)
            if ($teacher->photo && Storage::disk('public')->exists('photo/teachers/' . $teacher->photo)) {
                $photoPath = Storage::disk('public')->path('photo/teachers/' . $teacher->photo);
                $photo     = $this->manager->read($photoPath);
                $photo->resize(400, 400);
                $imageFront->place($photo, 'top-center', -2, 458);
            }

            // 3. Add Text (Front)
            $fontSize      = 24;
            $maxWidth      = $width - 100;
            $formattedName = $this->formatShortName($teacher->name, $maxWidth, $fontPath, $fontSize);

            $imageFront->text(strtoupper($formattedName), $width / 2, $height - 180, function ($font) use ($fontPath, $fontSize) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // NUPTK
            $imageFront->text("NUPTK: " . $teacher->nuptk, $width / 2, $height - 110, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(24);
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom');
            });

            // Pastikan folder ada
            if (!Storage::disk('public')->exists($subDir)) {
                Storage::disk('public')->makeDirectory($subDir);
            }

            // Save Front
            $pathFront = Storage::disk('public')->path($storagePathFront);
            $imageFront->save($pathFront);

            // --- BACK SIDE GENERATION ---

            $templateNameBack = CardTemplate::where('key', 'teacher_back')->value('file_name');

            if ($templateNameBack && file_exists(public_path('templates_card/' . $templateNameBack))) {
                $imageBack = $this->manager->read(public_path('templates_card/' . $templateNameBack));
            } else {
                $imageBack = $this->manager->create($width, $height)->fill('ffffff');
            }

            // 2. Add QR Code to Back
            $qrContent  = QrCode::format('png')->size(360)->generate($teacher->unique_code);
            $qrResource = @imagecreatefromstring($qrContent);
            if (!$qrResource) {
                throw new \Exception('Native GD imagecreatefromstring failed to read QR content.');
            }
            $qrImage = $this->manager->read($qrResource);

            // Terapkan Logo App dengan Background Putih Bulat
            $logoPath = settings('app_logo') ? public_path(settings('app_logo')) : null;
            if ($logoPath && file_exists($logoPath)) {
                $logoSize       = 80;
                $padding        = 2;
                $circleDiameter = $logoSize + ($padding * 2);

                $whiteCircleCanvas = $this->manager->create($circleDiameter, $circleDiameter);
                $whiteCircleCanvas->drawCircle($circleDiameter / 2, $circleDiameter / 2, function ($circle) use ($circleDiameter) {
                    $circle->radius(intval($circleDiameter / 2));
                    $circle->background('ffffff');
                });

                $logoImg = $this->manager->read($logoPath);
                $logoImg->resize($logoSize, $logoSize);

                $whiteCircleCanvas->place($logoImg, 'center');
                $qrImage->place($whiteCircleCanvas, 'center');
            }

            $imageBack->place($qrImage, 'center');

            // Save Back
            $pathBack = Storage::disk('public')->path($storagePathBack);
            $imageBack->save($pathBack);

            Log::info('Teacher ID Card (Front/Back) Saved Successfully');

            return [
                'success'   => true,
                'cached'    => false,
                'front_url' => asset('storage/' . $storagePathFront),
                'back_url'  => asset('storage/' . $storagePathBack),
                'url'       => asset('storage/' . $storagePathFront),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to generate Teacher ID Card', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to generate image: ' . $e->getMessage()];
        }
    }

    /**
     * Sanitasi string untuk digunakan sebagai nama file / folder.
     * Menghapus karakter selain huruf, angka, dan underscore. Spasi diganti underscore.
     */
    private function sanitizeFileName(string $string): string
    {
        // Hapus karakter khusus yang tidak aman untuk nama file
        $string = preg_replace('/[^A-Za-z0-9\s_\-]/', '', $string);
        // Ganti spasi (satu atau lebih) dengan underscore
        $string = preg_replace('/\s+/', '_', trim($string));
        return $string;
    }

    /**
     * Helper to shorten name based on text width and container limit.
     *
     * @param string $name
     * @param int    $widthLimit    The max allowed width in pixels
     * @param string $fontFilePath  The true type font path
     * @param int    $fontSize      The font size used
     * @return string
     */
    private function formatShortName(string $name, int $widthLimit, string $fontFilePath, int $fontSize): string
    {
        $name = trim($name);

        $box       = imagettfbbox($fontSize, 0, $fontFilePath, strtoupper($name));
        $textWidth = abs($box[4] - $box[0]);

        if ($textWidth <= $widthLimit) {
            return $name;
        }

        $words = explode(' ', $name);
        $count = count($words);

        if ($count <= 1) {
            return $name;
        }

        if ($count == 2) {
            $words[1] = mb_substr($words[1], 0, 1) . '.';
            return implode(' ', $words);
        }

        $firstWord      = $words[0];
        $lastWord       = $words[$count - 1];
        $middleInitials = [];

        for ($i = 1; $i < $count - 1; $i++) {
            $middleInitials[] = mb_substr($words[$i], 0, 1) . '.';
        }

        return $firstWord . ' ' . implode(' ', $middleInitials) . ' ' . $lastWord;
    }
}
