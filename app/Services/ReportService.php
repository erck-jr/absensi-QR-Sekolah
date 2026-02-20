<?php

namespace App\Services;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    /**
     * Generate structured data for monthly reports.
     * 
     * @param \Illuminate\Support\Collection $attendees Collection of Students or Teachers with 'attendances' loaded
     * @param int $month
     * @param int $year
     * @return array
     */
    public function generateMonthlyData($attendees, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $startDate->daysInMonth;
        
        // 1. Fetch Holidays
        $holidays = Holiday::whereMonth('dates', $month)
            ->whereYear('dates', $year)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->dates->format('Y-m-d') => $item->info];
            });

        // 2. Prepare Date Metadata
        $dates = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::createFromDate($year, $month, $d);
            $dateStr = $date->format('Y-m-d');
            
            $dates[$dateStr] = [
                'day' => $d,
                'is_sunday' => $date->isSunday(),
                'is_holiday' => $holidays->has($dateStr),
                'holiday_info' => $holidays->get($dateStr),
            ];
        }

        // 3. Process Attendees Rows
        $rows = [];
        foreach ($attendees as $attendee) {
            $dailyStatuses = [];
            $summary = [
                'H' => 0, 'I' => 0, 'S' => 0, 'A' => 0, 'T' => 0
            ];

            foreach ($dates as $dateStr => $meta) {
                // Default Cell Data
                $cell = [
                    'code' => '-',
                    'class' => '',
                ];

                if ($meta['is_holiday']) {
                    // Handled by View (Merged Row) or just skip
                    // We just set a flag or empty, view determines if it renders
                    $cell['code'] = 'LIBUR'; 
                } elseif ($meta['is_sunday']) {
                     $cell['code'] = '-';
                     $cell['class'] = 'text-gray-400';
                } else {
                    $attendance = $attendee->attendances->firstWhere('dates', Carbon::parse($dateStr));
                    
                    if ($attendance) {
                        $codeName = $attendance->attendanceCode->name;
                        
                        if ($codeName === 'Hadir') {
                            $cell['code'] = 'H';
                            $cell['class'] = 'bg-green-100 text-green-800';
                            $summary['H']++;
                            
                            if ($attendance->is_late) {
                                // T override H for display, but counts as H?
                                // Usually reports separate T. Let's follow previous logic:
                                // Previous logic: if is_late, code='T', h++ (still counts as present)
                                $cell['code'] = 'T';
                                $cell['class'] = 'bg-orange-100 text-orange-800';
                            }
                        } elseif ($codeName === 'Izin') {
                            $cell['code'] = 'I';
                            $cell['class'] = 'bg-yellow-100 text-yellow-800';
                            $summary['I']++;
                        } elseif ($codeName === 'Sakit') {
                            $cell['code'] = 'S';
                            $cell['class'] = 'bg-yellow-100 text-yellow-800';
                            $summary['S']++;
                        } elseif ($codeName === 'Alpha') {
                            $cell['code'] = 'A';
                            $cell['class'] = 'bg-red-100 text-red-800';
                            $summary['A']++;
                        }
                    } else {
                        // Auto Alpha
                        $cell['code'] = 'A';
                        $cell['class'] = 'bg-red-100 text-red-800';
                        $summary['A']++;
                    }
                }
                
                $dailyStatuses[$dateStr] = $cell;
            }

            $rows[] = [
                'id' => $attendee->id,
                'name' => $attendee->name,
                'nis_or_nuptk' => $attendee->nis ?? $attendee->nuptk ?? '-',
                'statuses' => $dailyStatuses,
                'summary' => $summary,
            ];
        }

        return [
            'dates' => $dates,
            'rows' => $rows,
            'attendee_count' => count($rows), // Important for rowspan
        ];
    }
}
