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
    protected $signature = 'wa:retry-expired {--limit=100 : Limit the number of logs to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry sending WhatsApp notifications for logs that are marked as expired';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expiredLogs = WaLog::where('status', 'expired')
            ->orderBy('created_at', 'asc')
            ->limit($this->option('limit'))
            ->get();

        if ($expiredLogs->isEmpty()) {
            $this->info('No expired WA logs found.');
            return;
        }

        $this->info("Found {$expiredLogs->count()} expired logs. Starting retry process...");

        foreach ($expiredLogs as $log) {
            $this->line("Processing Log ID: {$log->id} for Attendance ID: {$log->attendance_id} ({$log->attendance_type})");

            $attendance = $log->attendance_type === 'student'
                ? AttendanceStudent::with('shift')->find($log->attendance_id)
                : AttendanceTeacher::with('shift')->find($log->attendance_id);

            if (!$attendance) {
                $this->error("Attendance record not found for Log ID: {$log->id}. Skipping.");
                continue;
            }

            if (!$attendance->shift) {
                $this->error("Shift not found for Attendance ID: {$attendance->id}. Skipping.");
                continue;
            }

            $messageType = $this->determineMessageType($log, $attendance);
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

            // Delete old expired log to avoid duplicates/re-running
            $log->delete();
        }

        $this->info('All selected expired logs have been re-queued for sending.');
    }

    /**
     * Determine if the log was for check-in or check-out.
     */
    private function determineMessageType($log, $attendance)
    {
        // If no check-out exists, it must be a check-in
        if (!$attendance->check_out) {
            return 'check_in';
        }

        // If check-out exists, we check if the log was created close to the check-out update time
        // Note: logs are created almost instantly after updating attendance
        $logCreatedAt = $log->created_at;
        $attendanceUpdatedAt = $attendance->updated_at;

        // If log was created within 5 minutes of attendance being updated (and has check_out filled), it's likely check_out
        if ($logCreatedAt->diffInMinutes($attendanceUpdatedAt) < 5) {
            return 'check_out';
        }

        // Default or if log is much older than final update, assume it was the initial check-in that expired
        return 'check_in';
    }
}
