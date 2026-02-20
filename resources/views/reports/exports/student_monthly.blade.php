<table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
    <thead>
        <tr>
            <th colspan="{{ \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth + 6 }}" style="text-align: center; font-weight: bold; font-size: 16px; padding: 10px;">
                Laporan Kehadiran Siswa - Bulan {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
            </th>
        </tr>
        <tr>
            <th style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0;">No</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0; text-align: left;">Nama Siswa</th>
            @php
                $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
            @endphp
            @for($i = 1; $i <= $daysInMonth; $i++)
                <th style="border: 1px solid #000; padding: 5px; width: 30px; background-color: #f0f0f0;">{{ $i }}</th>
            @endfor
            <th style="border: 1px solid #000; padding: 5px; background-color: #d1fae5; color: #065f46;">H</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fef3c7; color: #92400e;">I</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fef3c7; color: #92400e;">S</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fee2e2; color: #991b1b;">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach($students as $index => $student)
            <tr>
                <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; padding: 5px;">{{ $student->name }}</td>
                
                @php
                    $h = 0; $i_count = 0; $s = 0; $a = 0;
                @endphp

                @for($d = 1; $d <= $daysInMonth; $d++)
                    @php
                        $currentDateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $currentDate = \Carbon\Carbon::parse($currentDateStr);
                        $att = $student->attendances->firstWhere('dates', $currentDate);
                        $code = '-';
                        $color = '';

                        if ($att) {
                            $name = $att->attendanceCode->name;
                            if ($name == 'Hadir') { $code = 'H'; $h++; $color='#d1fae5'; } // green-100
                            elseif ($name == 'Izin') { $code = 'I'; $i_count++;  $color='#fef3c7'; } // yellow-100
                            elseif ($name == 'Sakit') { $code = 'S'; $s++;  $color='#fef3c7'; } // yellow-100
                            elseif ($name == 'Alpha') { $code = 'A'; $a++;  $color='#fee2e2'; } // red-100
                            elseif ($att->is_late) { $code = 'T'; $h++; $color='#ffedd5'; } // orange-100
                        } else {
                            // Logic: If no attendance, check if it's Sunday (or Sat/Sun).
                            // User request: "jika kehadiran siswa tidak tercatat di tabel attendance maka dianggap alpha"
                            // If simplified: Just count A.
                            // I will exclude Sundays to be reasonable, or just simple logic?
                            // "kehadiran siswa tidak tercatat ... dianggap alpha". implies Auto-Alpha on standard days.
                            // Let's assume standard days = Mon-Sat or Mon-Fri? 
                            // Safest is to mark 'A' (red) but maybe check if Sunday?
                            // If I don't check Sunday, Sunday becomes A.
                            // I'll check Sunday.
                            if (!$currentDate->isSunday()) {
                                $code = 'A'; 
                                $a++;
                                $color = '#fee2e2'; 
                            }
                        }
                    @endphp
                    <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: {{ $color }};">{{ $code }}</td>
                @endfor
                
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #d1fae5;">{{ $h }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fef3c7;">{{ $i_count }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fef3c7;">{{ $s }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fee2e2;">{{ $a }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
