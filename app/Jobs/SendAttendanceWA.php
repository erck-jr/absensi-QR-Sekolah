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

    public int $tries = 3;
    public int $backoff = 5;

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
        // Ambil waktu pertama kali Job dibuat/dimasukkan ke antrian
        $jobCreatedAt = $this->job ? \Carbon\Carbon::createFromTimestamp($this->job->payload()['pushedAt'] ?? now()->timestamp) : now();

        // Dapatkan waktu riil saat siswa/guru melakukan absensi
        $attendanceTime = null;
        if ($this->attendance && !empty($this->attendance->dates)) {
            $timeString = $this->messageType === 'check_in' ? $this->attendance->check_in : $this->attendance->check_out;
            if (!empty($timeString)) {
                $attendanceDate = is_string($this->attendance->dates) 
                    ? $this->attendance->dates 
                    : $this->attendance->dates->format('Y-m-d');
                $attendanceTime = \Carbon\Carbon::parse($attendanceDate . ' ' . $timeString);
            }
        }
        $referenceTime = $attendanceTime ?? $jobCreatedAt;

        // ---------------------------------------------------------------------
        // 1. BATAS MAKSIMAL 1 HARI / 24 JAM (Pesan Hari Kemarin Batal Otomatis)
        // ---------------------------------------------------------------------
        if ($referenceTime->diffInHours(now()) >= 24) {
            $reason = 'Pesan dibatalkan: Waktu absensi sudah lebih dari 1 hari (24 jam)';
            $this->logToDb('expired', $reason);
            return; // Selesai normal, tidak perlu dikirim ke failed_jobs lagi
        }

        // ---------------------------------------------------------------------
        // 2. PENGECEKAN BATAS MAKSIMAL RETRY
        // ---------------------------------------------------------------------
        if ($this->attempts() > $this->tries) {
            $reason = "Percobaan Ulang Pengiriman WA sudah melewati batas maksimal {$this->tries}x Percobaan";
            $this->logToDb('failed', $reason);
            $this->fail(new \Exception($reason)); // Lempar ke failed_jobs
            return;
        }

        // ---------------------------------------------------------------------
        // 3. PENGECEKAN JEDA MAKSIMAL DARI WAKTU ABSENSI (MAKSIMAL 15 MENIT)
        // ---------------------------------------------------------------------
        if (!$this->ignoreExpiration && $referenceTime->diffInMinutes(now()) >= 15) {
            $reason = 'Pesan dibatalkan: Waktu absensi sudah lebih dari 15 menit yang lalu (' . $referenceTime->format('H:i:s') . ')';
            $this->logToDb('expired', $reason);
            return; // Selesai normal tanpa lempar fail(), agar failed_jobs tidak kotor
        }

        // ---------------------------------------------------------------------
        // 4. VALIDASI DATA GATEWAY & PENERIMA
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

        // Sanitasi nomor telepon: hapus karakter non-angka
        $rawPhone = $user->phone ?? '';
        $recipient = preg_replace('/[^0-9]/', '', $rawPhone);

        if (empty($recipient) || !preg_match('/^(0|62|\+62)/', $recipient)) {
            $reason = 'Format atau Nomor HP tidak valid: ' . ($rawPhone ?: 'kosong');
            $this->logToDb('failed', $reason, $gateway->id, $rawPhone);
            $this->fail(new \Exception($reason));
            return;
        }

        // Jeda acak alami (1.5 - 2.5 detik) untuk keamanan anti-ban & kelancaran antrian
        usleep(random_int(1500000, 2500000));

        // ---------------------------------------------------------------------
        // 5. PENYUSUNAN PESAN
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
                now()->format('H:i')
            ],
            $messageContent
        );

        $messageContent = self::parseSpintax($messageContent);

        // ---------------------------------------------------------------------
        // 6. EKSEKUSI PENGIRIMAN KE API ONESENDER
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
                $this->logToDb('sent', 'Sent successfully via OneSender', $gateway->id, $recipient, $messageContent);
            } else {
                throw new \Exception('API Error: ' . $response->body());
            }

        } catch (\Exception $e) {
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