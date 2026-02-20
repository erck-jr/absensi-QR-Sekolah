<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Top Row: Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Siswa Card -->
                <div class="bg-white rounded-lg shadow-md relative mt-4">
                    <div class="absolute -top-4 left-4 p-4 bg-purple-600 rounded-lg shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="p-4 text-right">
                        <p class="text-sm text-gray-500">Jumlah siswa</p>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $totalStudents }}</h3>
                    </div>
                    <div class="border-t border-gray-100 p-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="h-4 w-4 text-purple-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Terdaftar
                        </div>
                    </div>
                </div>

                <!-- Guru Card -->
                <div class="bg-white rounded-lg shadow-md relative mt-4">
                    <div class="absolute -top-4 left-4 p-4 bg-green-500 rounded-lg shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="p-4 text-right">
                        <p class="text-sm text-gray-500">Jumlah guru</p>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $totalTeachers }}</h3>
                    </div>
                    <div class="border-t border-gray-100 p-4">
                        <div class="flex items-center text-sm text-gray-500">
                            <svg class="h-4 w-4 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Terdaftar
                        </div>
                    </div>
                </div>

                <!-- Kelas Card -->
                <div class="bg-white rounded-lg shadow-md relative mt-4">
                    <div class="absolute -top-4 left-4 p-4 bg-blue-500 rounded-lg shadow-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div class="p-4 text-right">
                        <p class="text-sm text-gray-500">Jumlah kelas</p>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $totalClasses }}</h3>
                    </div>
                    <div class="border-t border-gray-100 p-4">
                         <div class="flex items-center text-sm text-gray-500">
                            <svg class="h-4 w-4 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ settings('school_name')}}
                        </div>
                    </div>
                </div>

                <!-- Petugas Card -->
                <div class="bg-white rounded-lg shadow-md relative mt-4">
                    <div class="absolute -top-4 left-4 p-4 bg-red-500 rounded-lg shadow-lg">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="p-4 text-right">
                        <p class="text-sm text-gray-500">Jumlah petugas</p>
                        <h3 class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</h3>
                    </div>
                    <div class="border-t border-gray-100 p-4">
                        <div class="flex items-center text-sm text-gray-500">
                             <svg class="h-4 w-4 text-red-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Petugas dan Administrator
                        </div>
                    </div>
                </div>
            </div>

            <!-- Middle Row: Daily Attendance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Absensi Siswa Hari Ini -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-purple-600 p-4 text-white">
                        <h3 class="text-lg font-semibold">Absensi Siswa Hari Ini</h3>
                        <p class="text-sm opacity-80">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-green-500 font-medium mb-1">Hadir</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $studentHadir }}</h4>
                        </div>
                        <div>
                            <p class="text-yellow-500 font-medium mb-1">Sakit</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $studentSakit }}</h4>
                        </div>
                        <div>
                            <p class="text-blue-500 font-medium mb-1">Izin</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $studentIzin }}</h4>
                        </div>
                         <div>
                            <p class="text-red-500 font-medium mb-1">Alpa</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $studentAlpa }}</h4>
                        </div>
                    </div>
                </div>

                <!-- Absensi Guru Hari Ini -->
                 <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-green-600 p-4 text-white">
                        <h3 class="text-lg font-semibold">Absensi Guru Hari Ini</h3>
                         <p class="text-sm opacity-80">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-green-500 font-medium mb-1">Hadir</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $teacherHadir }}</h4>
                        </div>
                        <div>
                            <p class="text-yellow-500 font-medium mb-1">Sakit</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $teacherSakit }}</h4>
                        </div>
                        <div>
                            <p class="text-blue-500 font-medium mb-1">Izin</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $teacherIzin }}</h4>
                        </div>
                         <div>
                            <p class="text-red-500 font-medium mb-1">Alpa</p>
                            <h4 class="text-2xl font-bold text-gray-700">{{ $teacherAlpa }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Chart Siswa -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="bg-purple-600 rounded-lg p-4 -mt-10 mb-4 shadow-lg">
                        <div id="studentChart" class="h-48 text-white"></div>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700">Tingkat kehadiran siswa</h3>
                    <p class="text-sm text-gray-500">Jumlah kehadiran siswa dalam 7 hari terakhir</p>
                    <div class="mt-4 border-t pt-2 text-sm text-purple-600 cursor-pointer">
                        <a href="{{ route('reports.students') }}">
                            <i class="fa fa-list"></i> Lihat data
                        </a>
                    </div>
                </div>

                <!-- Chart Guru -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="bg-green-500 rounded-lg p-4 -mt-10 mb-4 shadow-lg">
                         <div id="teacherChart" class="h-48 text-white"></div>
                    </div>
                     <h3 class="text-lg font-medium text-gray-700">Tingkat kehadiran guru</h3>
                    <p class="text-sm text-gray-500">Jumlah kehadiran guru dalam 7 hari terakhir</p>
                    <div class="mt-4 border-t pt-2 text-sm text-green-600 cursor-pointer">
                        <a href="{{ route('reports.teachers') }}">
                           <i class="fa fa-list"></i> Lihat data
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Student Chart
            var studentOptions = {
                series: [{
                    name: 'Hadir',
                    data: @json($studentTrend)
                }],
                chart: {
                    type: 'line',
                    height: 200,
                     toolbar: { show: false },
                     sparkline: { enabled: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: @json($dates),
                    labels: { style: { colors: '#fff' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    show: false
                },
                grid: {
                    show: true,
                    borderColor: 'rgba(255, 255, 255, 0.2)',
                    strokeDashArray: 3
                },
                colors: ['#fff'],
                tooltip: {
                    theme: 'dark'
                }
            };

            var studentChart = new ApexCharts(document.querySelector("#studentChart"), studentOptions);
            studentChart.render();

            // Teacher Chart
            var teacherOptions = {
                series: [{
                    name: 'Hadir',
                    data: @json($teacherTrend)
                }],
                chart: {
                    type: 'line',
                    height: 200,
                    toolbar: { show: false }
                },
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: ['#fff']
                },
                xaxis: {
                    categories: @json($dates),
                     labels: { style: { colors: '#fff' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    show: false
                },
                grid: {
                    show: true,
                     borderColor: 'rgba(255, 255, 255, 0.2)',
                    strokeDashArray: 3
                },
                 colors: ['#fff'],
                tooltip: {
                    theme: 'dark'
                }
            };

            var teacherChart = new ApexCharts(document.querySelector("#teacherChart"), teacherOptions);
            teacherChart.render();
        });
    </script>
    @endpush
</x-app-layout>
