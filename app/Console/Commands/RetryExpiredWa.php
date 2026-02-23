<?php

namespace App\Console\Commands;

use App\Models\WaLog;
use App\Models\AttendanceStudent;
use App\Models\AttendanceTeacher;
use App\Jobs\SendAttendanceWA;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RetryExpiredWa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:retry-expired {--limit=1000 : Limit the number of logs to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending WhatsApp notifications for logs that are marked as expired, with content-aware duplicate prevention';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $processedCount = 0;

        $this->info("Starting refined retry process for expired WA logs (Limit: {$limit})...");

        // Use chunking to handle large datasets efficiently
        WaLog::where('status', 'expired')
            ->orderBy('created_at', 'asc')
            ->chunk(100, function ($expiredLogs) use (&$processedCount, $limit) {
                foreach ($expiredLogs as $log) {
                    if ($processedCount >= $limit) {
                        return false; // Stop chunking
                    }

                    $this->line("--------------------------------------------------");
                    $this->line("Processing Log ID: {$log->id} | Attendance ID: {$log->attendance_id} ({$log->attendance_type})");

                    $attendance = $log->attendance_type === 'student'
                        ? AttendanceStudent::with('shift')->find($log->attendance_id)
                        : AttendanceTeacher::with('shift')->find($log->attendance_id);

                    if (!$attendance) {
                        $this->error("-> Attendance record not found. Deleting orphan log.");
                        $log->delete();
                        continue;
                    }

                    if (!$attendance->shift) {
                        $this->error("-> Shift not found for Attendance ID: {$attendance->id}. Skipping.");
                        continue;
                    }

                    // 1. Deduce Message Type (Check-in or Check-out)
                    $messageType = $this->determineMessageType($log, $attendance);
                    $this->line("-> Deduced Type: " . strtoupper($messageType));

                    // 2. Optimized Duplicate Check via Content Analysis
                    // We look at all "sent" logs for this attendance on that day
                    $sentLogs = WaLog::where('attendance_id', $log->attendance_id)
                        ->where('attendance_type', $log->attendance_type)
                        ->where('status', 'sent')
                        ->where('created_at', '>=', $log->created_at->startOfDay())
                        ->get();

                    $alreadySentThisType = false;
                    foreach ($sentLogs as $sentLog) {
                        if ($this->isSameMessageType($messageType, $sentLog->message_content)) {
                            $alreadySentThisType = true;
                            break;
                        }
                    }

                    if ($alreadySentThisType) {
                        $this->warn("-> A successful '{$messageType}' log already exists. Skipping.");
                        $log->delete(); 
                        continue;
                    }

                    // 3. Prepare parameters
                    $shift = $attendance->shift;
                    $isLate = false;
                    $isEarly = false;
                    
                    if ($messageType === 'check_in') {
                        $checkInLimit = Carbon::parse($shift->check_in_time)->addMinutes($shift->late_check_in_minute);
                        $isLate = Carbon::parse($attendance->check_in)->gt($checkInLimit);
                    } else {
                        if ($attendance->check_out) {
                            $checkOutTime = Carbon::parse($shift->check_out_time);
                            $isEarly = Carbon::parse($attendance->check_out)->lt($checkOutTime);
                        } else {
                            $this->error("-> No check-out time found for check_out log. Skipping.");
                            continue;
                        }
                    }

                    $this->info("-> Re-dispatching {$messageType} for {$log->attendance_type}.");

                    // 4. Dispatch job
                    SendAttendanceWA::dispatch(
                        $attendance,
                        $log->attendance_type,
                        $messageType,
                        $isLate,
                        $isEarly,
                        true // ignoreExpiration
                    );

                    // 5. Cleanup
                    $log->delete();
                    $processedCount++;
                }
            });

        $this->info("--------------------------------------------------");
        $this->info("Done. Processed {$processedCount} logs.");
    }

    /**
     * Determine if the log was for check-in or check-out based on timestamps and data.
     */
    private function determineMessageType($log, $attendance)
    {
        // If no check-out exists, it must be check-in
        if (!$attendance->check_out) {
            return 'check_in';
        }

        // Logic: logs are usually created shortly after the activity.
        // If the log was created BEFORE the check-out was recorded (or much earlier), it's check_in.
        // If it was created within a reasonable window of the update, it might be check_out.
        
        $logTime = $log->created_at;
        $checkInTime = Carbon::parse($attendance->dates->format('Y-m-d') . ' ' . $attendance->check_in);
        
        // If we have check_out, we check its timestamp if updated_at is reliable
        // Attendance record is updated when check_out is filled.
        $attendanceUpdatedAt = $attendance->updated_at;

        // If the log was created close to the final update of the record (when check_out was likely added)
        if ($logTime->diffInMinutes($attendanceUpdatedAt) < 15) {
            return 'check_out';
        }

        // Default to check_in if it looks old
        return 'check_in';
    }

    /**
     * Analyze message content to see if it matches the intended type.
     */
    private function isSameMessageType($type, $content)
    {
        $content = strtolower($content);
        
        if ($type === 'check_in') {
            // Keywords for Masuk
            return str_contains($content, 'absen masuk') || 
                   str_contains($content, 'masuk pada') || 
                   str_contains($content, 'terlambat');
        } else {
            // Keywords for Pulang
            return str_contains($content, 'absen pulang') || 
                   str_contains($content, 'pulang pada') || 
                   str_contains($content, 'pulang cepat');
        }
    }
}
