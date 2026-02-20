<table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
    <thead>
        <tr>
            <th colspan="{{ \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth + 6 }}" style="text-align: center; font-weight: bold; font-size: 16px; padding: 10px; text-transform: uppercase;">
                Laporan Kehadiran Siswa
            </th>
        </tr>
        <tr>
            <th colspan="{{ \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth + 6 }}" style="text-align: center; font-weight: bold; font-size: 16px; padding: 10px; text-transform: uppercase;">
                {{ settings('school_name', 'Nama Sekolah Belum Diatur') }}
            </th>
        </tr>
        <tr>
            <th rowspan="2" style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0; vertical-align: middle; text-align: center; font-weight: bold;">No</th>
            <th rowspan="2" style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0; text-align: left; vertical-align: middle; font-weight: bold;">Nama</th>
            <th colspan="{{ \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth }}" style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0; text-align: center; font-weight: bold;">
                Bulan {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
            </th>
            <th colspan="4" style="border: 1px solid #000; padding: 5px; background-color: #f0f0f0; text-align: center; font-weight: bold;">Rekap Jumlah Kehadiran</th>
        </tr>
        <tr>
            @php
                $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
            @endphp
            @for($i = 1; $i <= $daysInMonth; $i++)
                <th style="border: 1px solid #000; padding: 5px; width: 30px; background-color: #f0f0f0; text-align: center;">{{ $i }}</th>
            @endfor
            <th style="border: 1px solid #000; padding: 5px; background-color: #d1fae5; color: #065f46; text-align: center;">H</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fef3c7; color: #92400e; text-align: center;">I</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fef3c7; color: #92400e; text-align: center;">S</th>
            <th style="border: 1px solid #000; padding: 5px; background-color: #fee2e2; color: #991b1b; text-align: center;">A</th>
        </tr>
    </thead>
    <tbody>
        @foreach($reportData['rows'] as $index => $row)
            <tr>
                <td style="border: 1px solid #000; padding: 5px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid #000; padding: 5px;">{{ $row['name'] }}</td>
                
                @foreach($reportData['dates'] as $dateStr => $meta)
                    @if($meta['is_holiday'])
                        @if($loop->parent->first)
                        <td rowspan="{{ $reportData['attendee_count'] }}" style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #dbeafe; font-weight: bold; vertical-align: middle;">
                            <div style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; margin: 0 auto;">
                                {{ $meta['holiday_info'] }}
                            </div>
                        </td>
                        @endif
                    @else
                        @php
                            $code = $row['statuses'][$dateStr]['code'];
                            $color = '';
                            if ($code == 'H') $color = '#d1fae5';
                            elseif ($code == 'I') $color = '#fef3c7';
                            elseif ($code == 'S') $color = '#fef3c7';
                            elseif ($code == 'A') $color = '#fee2e2';
                            elseif ($code == 'T') $color = '#ffedd5';
                        @endphp
                        <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: {{ $color }};">{{ $code }}</td>
                    @endif
                @endforeach
                
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #d1fae5;">{{ $row['summary']['H'] }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fef3c7;">{{ $row['summary']['I'] }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fef3c7;">{{ $row['summary']['S'] }}</td>
                <td style="border: 1px solid #000; padding: 5px; text-align: center; background-color: #fee2e2;">{{ $row['summary']['A'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
