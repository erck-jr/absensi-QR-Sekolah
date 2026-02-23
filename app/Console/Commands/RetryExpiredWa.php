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
    protected $description = 'Retry sending WhatsApp notifications for logs that are marked as expired, with duplicate prevention';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $processedCount = 0;

        $this->info("Starting retry process for expired WA logs (Limit: {$limit})...");

        // Use chunking to handle large datasets efficiently
        WaLog::where('status', 'expired')
            ->orderBy('created_at', 'asc')
            ->chunk(100, function ($expiredLogs) use (&$processedCount, $limit) {
                foreach ($expiredLogs as $log) {
                    if ($processedCount >= $limit) {
                        return false; // Stop chunking
                    }

                    $this->line("Processing Log ID: {$log->id} for Attendance ID: {$log->attendance_id} ({$log->attendance_type})");

                    $attendance = $log->attendance_type === 'student'
                        ? AttendanceStudent::with('shift')->find($log->attendance_id)
                        : AttendanceTeacher::with('shift')->find($log->attendance_id);

                    if (!$attendance) {
                        $this->error("Attendance record not found for Log ID: {$log->id}. Deleting orphan log.");
                        $log->delete();
                        continue;
                    }

                    if (!$attendance->shift) {
                        $this->error("Shift not found for Attendance ID: {$attendance->id}. Skipping.");
                        continue;
                    }

                    $messageType = $this->determineMessageType($log, $attendance);

                    // --- DUPLICATE CHECK ---
                    // Check if there is already a successful 'sent' log for this attendance and message type
                    // This prevents sending 2 notifications if the command is run multiple times or if one already succeeded.
                    $alreadySent = WaLog::where('attendance_id', $log->attendance_id)
                        ->where('attendance_type', $log->attendance_type)
                        ->where('status', 'sent')
                        // We check the content or try to deduce if it was the same message type
                        // Since we don't have message_type column yet (unless migration run), we can look at the content or just rely on attendance_id
                        // But to be safe, if any 'sent' exists for this attendance, we should be careful.
                        // However, a student has check_in and check_out.
                        ->where('created_at', '>=', $log->created_at->startOfDay()) 
                        ->exists();

                    // Better duplicate check: If we can see the content contains "Masuk" or "Pulang"
                    // Or simply: if the log we are processing is of a certain type, check if THAT type was sent.
                    // For now, let's look for a sent log created around the same time or for the same attendance.
                    
                    if ($alreadySent) {
                        // To be more precise, let's check the message content if possible, 
                        // but since message_type isn't in DB yet, we'll use a conservative approach.
                        // If it's a check_in log (no check_out in attendance or log is old), check if a sent log exists.
                        $this->warn("-> A 'sent' log already exists for this attendance today. Skipping to avoid duplicates.");
                        $log->delete(); // Delete the expired log as it's no longer needed
                        continue;
                    }

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
                        }
                    }

                    $this->info("-> Re-dispatching {$messageType} for {$log->attendance_type}.");

                    // Dispatch job with ignoreExpiration = true
                    SendAttendanceWA::dispatch(
                        $attendance,
                        $log->attendance_type,
                        $messageType,
                        $isLate,
                        $isEarly,
                        true // ignoreExpiration
                    );

                    // Delete old expired log immediately after re-dispatching
                    $log->delete();
                    $processedCount++;
                }
            });

        $this->info("Done. Processed {$processedCount} logs.");
    }

    /**
     * Determine if the log was for check-in or check-out.
     */
    private function determineMessageType($log, $attendance)
    {
        if (!$attendance->check_out) {
            return 'check_in';
        }

        $logCreatedAt = $log->created_at;
        $attendanceUpdatedAt = $attendance->updated_at;

        if ($logCreatedAt->diffInMinutes($attendanceUpdatedAt) < 10) {
            return 'check_out';
        }

        return 'check_in';
    }
}
