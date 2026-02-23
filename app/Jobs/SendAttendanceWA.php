<?php

namespace App\Jobs;

use App\Models\MessageTemplate;
use App\Models\WaGateway;
use App\Models\WaLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendAttendanceWA implements ShouldQueue
{
    use Queueable;

    public $attendance;
    public string $type; // 'student' or 'teacher'
    public string $messageType; // 'check_in' or 'check_out'
    public bool $isLate;
    public bool $isEarly;

    /**
     * Create a new job instance.
     */
    public function __construct($attendance, string $type, string $messageType = 'check_in', bool $isLate = false, bool $isEarly = false)
    {
        $this->attendance = $attendance;
        $this->type = $type;
        $this->messageType = $messageType;
        $this->isLate = $isLate;
        $this->isEarly = $isEarly;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Check Gateway First (Avoid unnecessary sleep if inactive)
        $gateway = WaGateway::where('is_active', true)->first();
        if (!$gateway) {
            return;
        }

        // 2. Fixed Delay (2 seconds limit) to handle 1200+ scale without hitting 90min expiry
        sleep(2);

        // 3. Check Expiration (> 90 minutes)
        if ($this->attendance->created_at->diffInMinutes(now()) > 90) {
            $this->logToDb('expired', 'Message expired (older than 90 mins)');
            return;
        }

        // 4. Get Message Template Key
        $templateKey = $this->getTemplateKey();
        
        $template = MessageTemplate::where('key', $templateKey)->first();
        // Fallback to default if specific not found (optional, or just use generic text)
        $messageContent = $template ? $template->content : "Absensi {$this->messageType} berhasil.";

        // 5. Replace Placeholders
        $user = ($this->type === 'student') ? $this->attendance->student : $this->attendance->teacher;

        if (!$user) {
            $this->logToDb('failed', 'User record not found for ' . $this->type . ' ID: ' . ($this->attendance->student_id ?? $this->attendance->teacher_id));
            return;
        }
        
        $recipient = $user->phone;

        if (empty($recipient)) {
            $this->logToDb('failed', 'Recipient phone number is empty', $gateway->id);
            return;
        }

        // Validate phone format (starts with 0 or +6)
        if (!preg_match('/^(0|\+6)/', $recipient)) {
            $this->logToDb('failed', 'Format nomor HP tidak valid (harus diawali 0 atau +6): ' . $recipient, $gateway->id, $recipient);
            return;
        }

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

        // 6. Send to OneSender
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

        // Student
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
        // If no gatewayId is provided, try to find the first active one
        if (!$gatewayId) {
            $gateway = WaGateway::where('is_active', true)->first();
            $gatewayId = $gateway ? $gateway->id : null;
        }

        // Only log if we have a valid gateway or if the DB allows null (checked: schema says constrained, so gateway_id is likely required)
        if (!$gatewayId) {
            // Fallback: try to find ANY gateway if no active one exists
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
            // If absolutely no gateway exists, we can't log to wa_logs due to FK constraint
            // We'll just let it fail silently or log to Laravel logs
            \Illuminate\Support\Facades\Log::warning("Cannot log WA to DB: No Gateway found.");
        }
    }
}
