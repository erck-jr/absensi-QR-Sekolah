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
            $image->text(strtoupper($student->name), $width/2, $height - 185, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(30); // Adjusted size for better visibility
                $font->color('#000000');
                $font->align('center');
                $font->valign('bottom'); 
            });

            // NIS
            $image->text("NIS: " . $student->nis, $width/2, $height - 110, function ($font) use ($fontPath2) {
                $font->file($fontPath2);
                $font->size(30);
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
            
            // Place QR Code at bottom-center with center padding based on template
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
            if ($teacher->photo && \Illuminate\Support\Facades\Storage::exists('public/photo/teachers/' . $teacher->photo)) {
                $photoPath = \Illuminate\Support\Facades\Storage::path('public/photo/teachers/' . $teacher->photo);
                $photo = $this->manager->read($photoPath);
                
                // Resize photo to square and reasonable size (e.g. 300x300 or based on template design)
                // Assuming a standard design, let's make it 300x300 cover
                $photo->cover(300, 300);
                
                // Place photo. Position needs to be determined. 
                // Suggestion: Top-Center or specifically defined. 
                // For now, let's place it at top-center with some padding.
                $imageFront->place($photo, 'top-center', 0, 150);
            }

            // 3. Add Text (Front)
            // Name
            $imageFront->text(strtoupper($teacher->name), $width/2, $height - 185, function ($font) use ($fontPath) {
                $font->file($fontPath);
                $font->size(30);
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
}
