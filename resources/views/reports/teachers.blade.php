<x-app-layout>
    <x-slot name="title">Laporan Kehadiran Guru</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Kehadiran Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Laporan Kehadiran Guru" icon="assignment_ind" color="green">
                <!-- Filters -->
                <form method="GET" action="{{ route('reports.teachers') }}" class="mb-6 space-y-4" x-data="{ mode: '{{ request('mode', 'daily') }}' }">
                    <div class="flex space-x-4 mb-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="mode" value="daily" x-model="mode" class="form-radio text-green-600">
                            <span class="ml-2">Harian</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="mode" value="monthly" x-model="mode" class="form-radio text-green-600">
                            <span class="ml-2">Bulanan</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        
                        <!-- Daily Filters -->
                        <template x-if="mode === 'daily'">
                            <div class="contents">
                                <div>
                                    <label for="start_date" class="block mb-2 text-sm font-medium text-gray-900 ">Tanggal</label>
                                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                </div>
                            </div>
                        </template>

                        <!-- Monthly Filters -->
                        <template x-if="mode === 'monthly'">
                            <div class="contents">
                                <div>
                                    <label for="month" class="block mb-2 text-sm font-medium text-gray-900 ">Bulan</label>
                                    <select id="month" name="month" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                        @foreach(range(1, 12) as $m)
                                            <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="year" class="block mb-2 text-sm font-medium text-gray-900 ">Tahun</label>
                                    <select id="year" name="year" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                        @foreach(range(date('Y'), date('Y')-5) as $y)
                                            <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </template>
                        
                         <div x-show="mode !== 'monthly'">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900 ">Status</label>
                            <select id="status" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                <option value="">Semua Status</option>
                                <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Alpha" {{ request('status') == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                            </select>
                        </div>

                    </div>
                    
                    <div class="mt-4 flex justify-end">
                         <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center focus:outline-none">
                             <span class="material-icons text-sm mr-2">search</span> Tampilkan Laporan
                         </button>
                    </div>
                </form>

                @if($mode === 'monthly')
                @php
                    $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
                @endphp
                <div class="flex space-x-2 justify-end mb-4 no-print">
                    <a href="{{ route('reports.teachers.export', request()->all()) }}" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">description</span> Export Excel
                    </a>
                    <button onclick="printTable()" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">print</span> Cetak
                    </button>
                </div>
                @endif
                
                @if($mode === 'daily')
                 <div class="flex space-x-2 justify-end mb-4 no-print">
                    <a href="{{ route('reports.teachers.export-daily', request()->all()) }}" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">description</span> Export Excel
                    </a>
                    <button onclick="printTable()" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">print</span> Cetak
                    </button>
                </div>
                @endif

                <!-- Print Styles -->
                <style>
                    @media print {
                        .no-print { display: none !important; }
                        body * { visibility: hidden; }
                        #print-section, #print-section * { visibility: visible; }
                        #print-section { position: absolute; left: 0; top: 0; width: 100%; }
                        .overflow-x-auto { overflow: visible !important; }
                    }
                </style>

                <div class="relative overflow-x-auto" id="print-section">
                    <div class="p-4 text-center text-lg font-bold border-b bg-gray-50 uppercase text-gray-700">
                        @if($mode === 'monthly' && !$isFutureOrCurrent)
                            Laporan Kehadiran Guru - Bulan {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
                        @elseif($mode === 'daily')
                            Laporan Kehadiran Harian Guru - {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                        @else
                            Laporan Kehadiran Guru
                        @endif
                    </div>
                    <table id="search-table" class="w-full text-sm text-center text-gray-500 table-auto border-collapse">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                             @if($mode === 'daily')
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left">NUPTK</th>
                                <th scope="col" class="px-6 py-3 text-left">Nama Guru</th>
                                <th scope="col" class="px-6 py-3 text-left">Waktu Masuk</th>
                                <th scope="col" class="px-6 py-3 text-left">Waktu Pulang</th>
                                <th scope="col" class="px-6 py-3 text-left">Total Jam Kerja</th>
                                <th scope="col" class="px-6 py-3 text-left">Status</th>
                                <th scope="col" class="px-6 py-3 text-left">Keterangan</th>
                                <th scope="col" class="px-6 py-3 text-left">Aksi</th>
                            </tr>
                            @else
                             <tr>
                            <tr>
                                <th colspan="{{ $daysInMonth + 6 }}" class="px-6 py-4 text-center text-lg font-bold border bg-gray-50 uppercase text-gray-900">
                                    Laporan Kehadiran Guru
                                </th>
                            </tr>
                            <tr>
                                <th colspan="{{ $daysInMonth + 6 }}" class="px-6 py-2 text-center text-lg font-bold border bg-gray-50 uppercase text-gray-900">
                                    {{ settings('school_name', 'Nama Sekolah Belum Diatur') }}
                                </th>
                            </tr>
                            <tr>
                                <th rowspan="2" scope="col" class="px-2 py-3 border sticky left-0 bg-gray-50 text-center font-bold align-middle">No</th>
                                <th rowspan="2" scope="col" class="px-4 py-3 border text-left sticky left-10 bg-gray-50 align-middle font-bold min-w-[200px]">Nama Guru</th>
                                <th colspan="{{ $daysInMonth }}" scope="col" class="px-1 py-3 border bg-gray-50 text-center font-bold">
                                    Bulan {{ \Carbon\Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y') }}
                                </th>
                                <th colspan="4" scope="col" class="px-2 py-3 border bg-gray-50 text-center font-bold">Rekap Jumlah Kehadiran</th>
                            </tr>
                            <tr>
                                @foreach($reportData['dates'] as $dateStr => $meta)
                                    <th scope="col" class="px-1 py-3 border w-8 text-center">{{ $meta['day'] }}</th>
                                @endforeach
                                <th scope="col" class="px-2 py-3 border bg-green-100 text-green-800 text-center">H</th>
                                <th scope="col" class="px-2 py-3 border bg-yellow-100 text-yellow-800 text-center">I</th>
                                <th scope="col" class="px-2 py-3 border bg-yellow-100 text-yellow-800 text-center">S</th>
                                <th scope="col" class="px-2 py-3 border bg-red-100 text-red-800 text-center">A</th>
                            </tr>
                            @endif
                        </thead>
                        <tbody>
                             @if($mode === 'daily')
                                @foreach($teachers as $teacher)
                                    @php
                                        // ... existing daily logic ...
                                        $attendance = $teacher->attendances->first();
                                        $statusText = 'Belum Absensi';
                                        $statusClass = 'bg-gray-100 text-gray-800';
                                        
                                        if ($attendance) {
                                            $statusText = $attendance->attendanceCode->name;
                                            if ($statusText === 'Hadir') $statusClass = 'bg-green-100 text-green-800';
                                            elseif ($statusText === 'Izin' || $statusText === 'Sakit') $statusClass = 'bg-yellow-100 text-yellow-800';
                                            elseif ($statusText === 'Alpha') $statusClass = 'bg-red-100 text-red-800';
                                            elseif ($attendance->is_late) $statusClass = 'bg-orange-100 text-orange-800';
                                        } else {
                                            $today = \Carbon\Carbon::today()->toDateString();
                                            if ($date > $today) {
                                                $statusText = 'Belum Tersedia';
                                                $statusClass = 'bg-gray-100 text-gray-800';
                                            } elseif ($date < $today) {
                                                $statusText = 'Tidak Hadir (Alpa)';
                                                $statusClass = 'bg-red-100 text-red-800';
                                            }
                                        }

                                    @endphp
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4 text-left">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-left">{{ $teacher->nuptk }}</td>
                                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap text-left">{{ $teacher->name }}</td>
                                        <td class="px-6 py-4 text-left">{{ ($attendance && $attendance->check_in) ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 text-left">{{ ($attendance && $attendance->check_out) ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 text-left">{{ $attendance ? $attendance->work_duration : '-' }}</td>
                                        <td class="px-6 py-4 text-left">
                                            <span class="{{ $statusClass }} text-xs font-medium mr-2 px-2.5 py-0.5 rounded">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-left">{{ $attendance->note ?? '-' }}</td>
                                        <td class="px-6 py-4 text-left">
                                            @if(auth()->user()->role === 'admin')
                                            <button type="button" 
                                                onclick="openModal('{{ $teacher->id }}', '{{ $teacher->name }}', '{{ $attendance ? $attendance->attendance_id : '' }}', '{{ $attendance ? $attendance->shift_id : '' }}', '{{ $attendance ? $attendance->check_in : '' }}', '{{ $attendance->note ?? '' }}')"
                                                class="font-medium text-blue-600 hover:underline">
                                                Update Status
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                @if($isFutureOrCurrent)
                                <tr>
                                    <td colspan="{{ 31 + 4 + 2 }}" class="px-6 py-4 text-center text-gray-500">
                                        Laporan belum tersedia. Bulan ini sedang berjalan.
                                    </td>
                                </tr>
                            @else
                                @if($reportData)
                                    @foreach($reportData['rows'] as $index => $row)
                                        <tr class="bg-white border-b">
                                            <td class="px-2 py-3 border sticky left-0 bg-white">{{ $loop->iteration }}</td>
                                            <td class="px-4 py-3 border text-left sticky left-10 bg-white font-medium text-gray-900 whitespace-nowrap">{{ $row['name'] }}</td>
                                            
                                            @foreach($reportData['dates'] as $dateStr => $meta)
                                                @if($meta['is_holiday'])
                                                    @if($loop->parent->first)
                                                        <td rowspan="{{ $reportData['attendee_count'] }}" class="px-1 py-1 border text-center text-xs bg-blue-100 text-blue-800 font-bold" style="vertical-align: middle;">
                                                            <div style="writing-mode: vertical-rl; transform: rotate(180deg); white-space: nowrap; margin: 0 auto; min-height: 100px;">
                                                                {{ $meta['holiday_info'] }}
                                                            </div>
                                                        </td>
                                                    @endif
                                                @else
                                                     <td class="px-1 py-1 border text-center text-xs {{ $row['statuses'][$dateStr]['class'] }}">
                                                         {{ $row['statuses'][$dateStr]['code'] }}
                                                     </td>
                                                @endif
                                            @endforeach
                                            
                                            <td class="px-2 py-3 border font-bold bg-green-50 text-green-900">{{ $row['summary']['H'] }}</td>
                                            <td class="px-2 py-3 border font-bold bg-yellow-50 text-yellow-900">{{ $row['summary']['S'] }}</td>
                                            <td class="px-2 py-3 border font-bold bg-blue-50 text-blue-900">{{ $row['summary']['I'] }}</td>
                                            <td class="px-2 py-3 border font-bold bg-red-50 text-red-900">{{ $row['summary']['A'] }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                         <td colspan="100" class="text-center py-4">Tidak ada data.</td>
                                    </tr>
                                @endif
                            @endif
                            @endif
                        </tbody>
                    </table>
                </div>

            </x-material-card>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateStatusModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="relative w-full max-w-md max-h-full">
            <div class="relative bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 uppercase">
                <button type="button" onclick="closeModal()" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center  ">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="px-6 py-6 lg:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 ">Update Status Kehadiran Guru</h3>
                    <form class="space-y-6" action="{{ route('reports.teachers.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date" value="{{ $date ?? date('Y-m-d') }}">
                        <input type="hidden" name="teacher_id" id="modal_teacher_id">
                        
                        <div>
                            <label for="modal_teacher_name" class="block mb-2 text-sm font-medium text-gray-900 ">Nama Guru</label>
                            <input type="text" id="modal_teacher_name" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 cursor-not-allowed" disabled>
                        </div>
                        
                        <div>
                            <label for="modal_attendance_code" class="block mb-2 text-sm font-medium text-gray-900 ">Status</label>
                            <select name="attendance_code_id" id="modal_attendance_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required onchange="toggleInputs()">
                                @foreach($attendanceCodes ?? [] as $code)
                                    <option value="{{ $code->id }}" data-name="{{ $code->name }}">{{ $code->name }}</option>
                                @endforeach
                            </select>
                        </div>

                         <div id="shift_container">
                            <label for="modal_shift" class="block mb-2 text-sm font-medium text-gray-900 ">Shift</label>
                            <select name="shift_id" id="modal_shift" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                @foreach($shifts ?? [] as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->check_in_time }} - {{ $shift->check_out_time }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="check_in_container">
                            <label for="modal_check_in" class="block mb-2 text-sm font-medium text-gray-900 ">Waktu Masuk</label>
                            <input type="time" name="check_in" id="modal_check_in" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>

                        <div>
                            <label for="modal_note" class="block mb-2 text-sm font-medium text-gray-900 ">Keterangan</label>
                            <textarea name="note" id="modal_note" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                        </div>

                        <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center   ">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(teacherId, teacherName, attendanceCodeId, shiftId, checkIn, note) {
            document.getElementById('modal_teacher_id').value = teacherId;
            document.getElementById('modal_teacher_name').value = teacherName;
            document.getElementById('modal_note').value = note;
            
            if (attendanceCodeId) {
                document.getElementById('modal_attendance_code').value = attendanceCodeId;
            } else {
                 const select = document.getElementById('modal_attendance_code');
                 if (select.options.length > 0) select.selectedIndex = 0;
            }
            
            if (shiftId) {
                document.getElementById('modal_shift').value = shiftId;
            } else {
                 const shiftSelect = document.getElementById('modal_shift');
                 if(shiftSelect.options.length > 0) shiftSelect.selectedIndex = 0;
            }

            if (checkIn) {
                document.getElementById('modal_check_in').value = checkIn.substring(0, 5);
            } else {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                document.getElementById('modal_check_in').value = `${hours}:${minutes}`;
            }

            toggleInputs();
            document.getElementById('updateStatusModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('updateStatusModal').classList.add('hidden');
        }

        function printTable() {
            const table = document.getElementById('search-table');
            if (window.SimpleDataTables && window.SimpleDataTables[table.id]) {
                const dt = window.SimpleDataTables[table.id];
                dt.destroy();
                setTimeout(() => {
                    window.print();
                    window.location.reload();
                }, 500);
            } else {
                window.print();
            }
        }

        function toggleInputs() {
            const select = document.getElementById('modal_attendance_code');
            const selectedOption = select.options[select.selectedIndex];
            const statusName = selectedOption.getAttribute('data-name');
            
            const checkInContainer = document.getElementById('check_in_container');
            const checkInInput = document.getElementById('modal_check_in');
            const noteInput = document.getElementById('modal_note');

            if (statusName === 'Hadir') {
                checkInContainer.classList.remove('hidden');
                checkInInput.required = true;
                noteInput.required = false;
            } else {
                checkInContainer.classList.add('hidden');
                checkInInput.required = false;
                noteInput.required = true;
            }
        }
    </script>
</x-app-layout>

