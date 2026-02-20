<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\MessageTemplate;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $templates = [
            'student_checkin' => 'Halo {name}, Anda telah melakukan absen masuk pada {time}. Status: {status}.',
            'student_checkout' => 'Halo {name}, Anda telah melakukan absen pulang pada {time}. Terima kasih.',
            'teacher_checkin' => 'Yth. {name}, absen masuk Anda tercatat pada {time}. Status: {status}.',
            'teacher_checkout' => 'Yth. {name}, absen pulang Anda tercatat pada {time}. Selamat beristirahat.',
            'student_late_checkin' => 'Halo {name}, Anda terlambat melakukan absen masuk pada {time}. Mohon lebih tepat waktu.',
            'student_early_checkout' => 'Halo {name}, Anda melakukan absen pulang lebih awal pada {time}.',
        ];

        foreach ($templates as $key => $content) {
            MessageTemplate::updateOrCreate(
                ['key' => $key],
                ['content' => $content]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Remove templates if needed, but usually we leave data
    }
};
