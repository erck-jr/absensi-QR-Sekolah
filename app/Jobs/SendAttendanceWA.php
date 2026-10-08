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
     * Batas maksimal pengulangan job oleh Laravel.
     */
    public int $tries = 10;

    /**
     * Waktu tunggu (detik) sebelum job dicoba ulang jika melempar exception.
     */
    public int $backoff = 10;

    public $attendance;
    public string $type; // 'student' or 'teacher'
    public string $messageType; // 'check_in' or 'check_out'
    public bool $isLate;
    public bool $isEarly;
    public bool $ignoreExpiration;

    public function __construct($attendance, string $type, string $messageType = 'check_in', bool $isLate = false, bool $isEarly = false, bool $ignoreExpiration = false)
    {
        $this->attendance = $attendance;
        $this->type = $type;
        $this->messageType = $messageType;
        $this->isLate = $isLate;
        $this->isEarly = $isEarly;
        $this->ignoreExpiration = $ignoreExpiration;
    }

    public function middleware(): array
    {
        return [new RateLimited('wa-gateway-limiter')];
    }

    public function handle(): void
    {
        // Ambil waktu pertama kali Job dimasukkan ke antrian
        $jobCreatedAt = $this->job ? \Carbon\Carbon::createFromTimestamp($this->job->getTimestamp()) : now();

        // ---------------------------------------------------------------------
        // 1. PENGECEKAN BATAS MAKSIMAL RETRY (> 10 kali)
        // ---------------------------------------------------------------------
        if ($this->attempts() > 10) {
            $reason = 'Percobaan Ulang Pengiriman WA sudah melewati batas maksimal 10x Percobaan';
            $this->logToDb('failed', $reason);
            $this->fail(new \Exception($reason)); // Lempar ke failed_jobs
            return;
        }

        // ---------------------------------------------------------------------
        // 2. PENGECEKAN LAMA ANTRIAN DI QUEUE (>= 30 Menit)
        // ---------------------------------------------------------------------
        if ($jobCreatedAt->diffInMinutes(now()) >= 30) {
            $reason = 'Waktu Tunggu Job Pengiriman WA untuk dieksekusi sudah lebih dari 30 menit';
            $this->logToDb('expired', $reason);
            $this->fail(new \Exception($reason)); // Lempar ke failed_jobs untuk retry manual oleh admin
            return;
        }

        // ---------------------------------------------------------------------
        // 3. VALIDASI DATA GATEWAY & PENERIMA
        // ---------------------------------------------------------------------
        $gateway = WaGateway::where('is_active', true)->first();
        if (!$gateway) {
            throw new \Exception('Tidak ada Gateway WA yang aktif.');
        }

        $user = ($this->type === 'student') ? $this->attendance->student : $this->attendance->teacher;
        if (!$user) {
            $reason = 'User record not found for ' . $this->type . ' ID: ' . ($this->attendance->student_id ?? $this->attendance->teacher_id);
            $this->logToDb('failed', $reason, $gateway->id);
            $this->fail(new \Exception($reason));
            return;
        }

        $recipient = $user->phone;

        if (empty($recipient) || !preg_match('/^(0|62|\+62)/', $recipient)) {
            $reason = 'Format atau Nomor HP tidak valid: ' . ($recipient ?? 'kosong');
            $this->logToDb('failed', $reason, $gateway->id, $recipient);
            $this->fail(new \Exception($reason));
            return;
        }

        // Anti-Ban Jeda Acak Singkat (2 - 4 detik)
        sleep(rand(2, 4));

        // ---------------------------------------------------------------------
        // 4. PENYUSUNAN PESAN
        // ---------------------------------------------------------------------
        $templateKey = $this->getTemplateKey();
        $template = MessageTemplate::where('key', $templateKey)->first();
        $messageContent = $template ? $template->content : "Absensi {$this->messageType} berhasil.";

        $messageContent = str_replace(
            ['{name}', '{nis}', '{nuptk}', '{time}', '{date}', '{status}', '{sent_time}'],
            [
                $user->name,
                $this->type == 'student' ? ($user->nis ?? '-') : '-',
                $this->type == 'teacher' ? ($user->nuptk ?? '-') : '-',
                \Carbon\Carbon::parse($this->messageType == 'check_in' ? $this->attendance->check_in : $this->attendance->check_out)->format('H:i:s'),
                \Carbon\Carbon::parse($this->attendance->dates)->format('d-m-Y'),
                $this->getStatusLabel(),
                now()->format('H:i') // Mengisi placeholder {sent_time} dengan waktu eksekusi
            ],
            $messageContent
        );

        $messageContent = self::parseSpintax($messageContent);

        // ---------------------------------------------------------------------
        // 5. EKSEKUSI PENGIRIMAN KE API ONESENDER
        // ---------------------------------------------------------------------
        try {
            $response = Http::timeout(10)->withHeaders([
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
                // Tulis Log SENT hanya jika pesan berhasil terkirim
                $this->logToDb('sent', 'Sent successfully via OneSender', $gateway->id, $recipient, $messageContent);
            } else {
                // Jika API Error, throw exception agar di-retry otomatis tanpa catat log dulu
                throw new \Exception('API Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            // Lemparkan exception agar dicoba ulang di antrian selama tries <= 10 dan waktu < 30 menit
            throw $e;
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