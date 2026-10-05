<?php

namespace App\Jobs;

use App\Models\MessageTemplate;
use App\Models\WaGateway;
use App\Models\WaLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendAttendanceWA implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah batas percobaan job sebelum dinyatakan gagal total.
     */
    public int $tries = 100;

    /**
     * Waktu tunggu (detik) jika terjadi exception yang tidak tertangkap.
     */
    public int $backoff = 10;

    public $attendance;
    public string $type; // 'student' or 'teacher'
    public string $messageType; // 'check_in' or 'check_out'
    public bool $isLate;
    public bool $isEarly;
    public bool $ignoreExpiration;

    /**
     * Create a new job instance.
     */
    public function __construct($attendance, string $type, string $messageType = 'check_in', bool $isLate = false, bool $isEarly = false, bool $ignoreExpiration = false)
    {
        $this->attendance = $attendance;
        $this->type = $type;
        $this->messageType = $messageType;
        $this->isLate = $isLate;
        $this->isEarly = $isEarly;
        $this->ignoreExpiration = $ignoreExpiration;
    }

    /**
     * Dapatkan middleware yang harus dilalui oleh job.
     */
    public function middleware(): array
    {
        return [new RateLimited('wa-gateway-limiter')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Check Gateway First
        $gateway = WaGateway::where('is_active', true)->first();
        if (!$gateway) {
            Log::warning("Pesan WA tidak terkirim: Tidak ada Gateway WA yang aktif.");
            return;
        }

        // 2. Fetch User & Recipient Early
        $user = ($this->type === 'student') ? $this->attendance->student : $this->attendance->teacher;

        if (!$user) {
            $this->logToDb('failed', 'User record not found for ' . $this->type . ' ID: ' . ($this->attendance->student_id ?? $this->attendance->teacher_id), $gateway->id);
            return;
        }
        
        $recipient = $user->phone;

        // 3. Check Expiration (> 90 minutes)
        $timestamp = ($this->messageType === 'check_in') ? $this->attendance->created_at : $this->attendance->updated_at;
        
        if (!$this->ignoreExpiration && $timestamp->diffInMinutes(now()) > 90) {
            $this->logToDb('expired', 'Message expired (older than 90 mins)', $gateway->id, $recipient);
            return;
        }

        // 4. Validate Phone Number
        if (empty($recipient)) {
            $this->logToDb('failed', 'Recipient phone number is empty', $gateway->id);
            return;
        }

        if (!preg_match('/^(0|62|\+62)/', $recipient)) {
            $this->logToDb('failed', 'Format nomor HP tidak valid: ' . $recipient, $gateway->id, $recipient);
            return;
        }

        // 5. Anti-Ban Jeda Acak Singkat (2 - 5 detik antar eksekusi)
        sleep(rand(2, 5));

        // 6. Get Message Template Key & Prepare Content
        $templateKey = $this->getTemplateKey();
        $template = MessageTemplate::where('key', $templateKey)->first();
        $messageContent = $template ? $template->content : "Absensi {$this->messageType} berhasil.";

        $messageContent = str_replace(
            ['{name}', '{nis}', '{nuptk}', '{time}', '{date}', '{status}'],
            [
                $user->name,
                $this->type == 'student' ? ($user->nis ?? '-') : '-',
                $this->type == 'teacher' ? ($user->nuptk ?? '-') : '-',
                \Carbon\Carbon::parse($this->messageType == 'check_in' ? $this->attendance->check_in : $this->attendance->check_out)->format('H:i:s'), 
                \Carbon\Carbon::parse($this->attendance->dates)->format('d-m-Y'),
                $this->getStatusLabel()
            ],
            $messageContent
        );

        // Parse Spintax {A|B|C}
        $messageContent = self::parseSpintax($messageContent);

        // 7. Send to OneSender
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $gateway->api_token,
            ])->post($gateway->api_url, [
                'recipient_type' => 'individual',
                'to' => $recipient,
                'type' => 'text',
                'text' => [
                    'body' => $messageContent
                ]
            ]);

            if ($response->successful()) {
                $this->logToDb('sent', 'Sent successfully via OneSender', $gateway->id, $recipient, $messageContent);
            } else {
                $this->logToDb('failed', 'API Error: ' . $response->body(), $gateway->id, $recipient, $messageContent);
            }

        } catch (\Exception $e) {
            $this->logToDb('failed', 'Exception: ' . $e->getMessage(), $gateway->id, $recipient, $messageContent);
        }
    }

    private function getTemplateKey(): string
    {
        if ($this->type === 'teacher') {
            return $this->messageType == 'check_in' ? 'teacher_checkin' : 'teacher_checkout';
        }

        if ($this->messageType == 'check_in') {
            return $this->isLate ? 'student_late_checkin' : 'student_checkin';
        } else {
            return $this->isEarly ? 'student_early_checkout' : 'student_checkout';
        }
    }

    private function getStatusLabel(): string
    {
        if ($this->messageType == 'check_in') {
            return $this->isLate ? 'Terlambat' : 'Tepat Waktu';
        } else {
            return $this->isEarly ? 'Pulang Cepat' : 'Pulang Normal';
        }
    }

    private function logToDb($status, $details, $gatewayId = null, $recipient = null, $content = null)
    {
        if (!$gatewayId) {
            $gateway = WaGateway::where('is_active', true)->first();
            $gatewayId = $gateway ? $gateway->id : null;
        }

        if (!$gatewayId) {
            $gateway = WaGateway::first();
            $gatewayId = $gateway ? $gateway->id : null;
        }

        if ($gatewayId) {
            WaLog::create([
                'attendance_id' => $this->attendance->id,
                'attendance_type' => $this->type,
                'gateway_id' => $gatewayId,
                'recipient_number' => $recipient ?? 'unknown',
                'message_content' => $content ?? '',
                'status' => $status,
                'error_details' => $details,
            ]);
        } else {
            Log::warning("Cannot log WA to DB: No Gateway found.");
        }
    }

    /**
     * Parse Spintax {Option1|Option2|Option3} recursively.
     */
    public static function parseSpintax($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            function ($text) {
                $text = self::parseSpintax($text[1]);
                $parts = explode('|', $text);
                return $parts[array_rand($parts)];
            },
            $text
        );
    }
}