<x-app-layout>
    <x-slot name="title">Pengaturan Sistem</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Sistem') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Pengaturan Sistem" icon="settings" color="gray">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Aplikasi</h3>
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="app_name" class="block mb-2 text-sm font-medium text-gray-900">Nama Aplikasi</label>
                            <input type="text" id="app_name" name="app_name" value="{{ $settings['app_name'] ?? '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Contoh: Absensi QR Sekolah">
                        </div>
                        <div>
                            <label for="school_name" class="block mb-2 text-sm font-medium text-gray-900">Nama Sekolah / Instansi</label>
                            <input type="text" id="school_name" name="school_name" value="{{ $settings['school_name'] ?? '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Contoh: SMA Negeri 1 ...">
                        </div>
                        <div class="md:col-span-2">
                            <label for="app_description" class="block mb-2 text-sm font-medium text-gray-900">Deskripsi Aplikasi</label>
                            <input type="text" id="app_description" name="app_description" value="{{ $settings['app_description'] ?? '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Deskripsi singkat yang muncul di layout samping...">
                        </div>
                        <div class="md:col-span-2">
                            <label for="welcome_text" class="block mb-2 text-sm font-medium text-gray-900">Teks Welcome (Halaman Depan)</label>
                            <textarea id="welcome_text" name="welcome_text" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Teks yang akan muncul di card selamat datang...">{{ $settings['welcome_text'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label for="school_address" class="block mb-2 text-sm font-medium text-gray-900">Alamat Instansi</label>
                            <input type="text" id="school_address" name="school_address" value="{{ $settings['school_address'] ?? '' }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                             <label for="app_logo" class="block mb-2 text-sm font-medium text-gray-900">Logo Aplikasi</label>
                             @if(isset($settings['app_logo']))
                                <div class="mb-2">
                                    <img src="{{ asset($settings['app_logo']) }}" alt="Current Logo" class="h-16 w-auto object-contain border rounded p-1">
                                </div>
                             @endif
                             <input type="file" id="app_logo" name="app_logo" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                             <p class="mt-1 text-sm text-gray-500">SVG, PNG, JPG or GIF (MAX. 2MB).</p>
                        </div>
                    </div>

                    @php
                        $savedWeekendDays = isset($settings['weekend_days']) ? (json_decode($settings['weekend_days'], true) ?: []) : [];
                    @endphp

                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4 mt-8 flex items-center">
                        <span class="material-icons text-blue-600 mr-2 text-xl">event_busy</span>
                        Hari Libur Akhir Pekan (Weekend)
                    </h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Tandai hari di bawah ini sebagai hari libur akhir pekan. Hari yang ditandai akan dianggap hari libur dan tidak masuk dalam perhitungan rekap absensi (siswa dan guru tidak tercatat Alpa).
                        <span class="font-semibold text-gray-700">*Catatan: Hari Minggu sudah otomatis menjadi hari libur tetap.</span>
                    </p>

                    <div class="grid gap-4 mb-6 sm:grid-cols-2">
                        <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <input id="weekend_friday" type="checkbox" name="weekend_days[]" value="friday" 
                                {{ in_array('friday', $savedWeekendDays) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                            <label for="weekend_friday" class="ml-3 text-sm font-medium text-gray-900 cursor-pointer">
                                <span class="font-bold text-base block">Hari Jumat</span>
                                <span class="text-xs text-gray-500">Tandai hari Jumat sebagai hari libur akhir pekan</span>
                            </label>
                        </div>
                        <div class="flex items-center p-4 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors">
                            <input id="weekend_saturday" type="checkbox" name="weekend_days[]" value="saturday" 
                                {{ in_array('saturday', $savedWeekendDays) ? 'checked' : '' }}
                                class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                            <label for="weekend_saturday" class="ml-3 text-sm font-medium text-gray-900 cursor-pointer">
                                <span class="font-bold text-base block">Hari Sabtu</span>
                                <span class="text-xs text-gray-500">Tandai hari Sabtu sebagai hari libur akhir pekan (sekolah 5 hari kerja)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
