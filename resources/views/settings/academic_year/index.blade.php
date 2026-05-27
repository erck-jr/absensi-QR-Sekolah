<x-app-layout>
    <x-slot name="title">Tahun Ajaran Baru & Manajemen Database</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tahun Ajaran Baru & Utilitas Database') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
                    <span class="font-medium">Sukses!</span> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                    <span class="font-medium">Error!</span> {{ session('error') }}
                </div>
            @endif

            @if(session('success_cleanup'))
                <div class="p-5 mb-4 text-sm text-green-800 rounded-xl bg-green-50 border border-green-200 shadow-md" role="alert">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="material-icons text-green-600">check_circle</span>
                        <span class="font-bold text-base">Pembersihan Selesai!</span>
                    </div>
                    <p class="mb-3">{{ session('success_cleanup')['message'] }}</p>
                    <div class="bg-white p-3 rounded-lg border inline-flex items-center space-x-3 shadow-sm hover:bg-gray-50">
                        <span class="material-icons text-blue-600">download</span>
                        <div>
                            <p class="text-xs text-gray-400 font-bold">SILAKAN UNDUH BACKUP ANDA:</p>
                            <a href="{{ session('success_cleanup')['download_url'] }}" class="text-blue-600 hover:text-blue-800 font-bold underline text-sm">
                                {{ session('success_cleanup')['filename'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="tabs" role="tablist">
                    <li class="mr-2" role="presentation">
                        <button class="inline-flex items-center p-4 border-b-2 rounded-t-lg active text-blue-600 border-blue-600 hover:text-blue-600 group" 
                            id="promotion-tab" data-target="#promotion-content" type="button" role="tab">
                            <span class="material-icons mr-2 text-sm">stairs</span>
                            Kenaikan Kelas & Kelulusan
                        </button>
                    </li>
                    <li class="mr-2" role="presentation">
                        <button class="inline-flex items-center p-4 border-b-2 rounded-t-lg border-transparent text-gray-500 hover:text-gray-600 hover:border-gray-300 group" 
                            id="cleanup-tab" data-target="#cleanup-content" type="button" role="tab">
                            <span class="material-icons mr-2 text-sm">settings_backup_restore</span>
                            Backup & Bersihkan Absensi
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Tab Content Container -->
            <div id="tab-contents">
                
                <!-- Tab 1: Kenaikan Kelas & Kelulusan -->
                <div class="space-y-6" id="promotion-content" role="tabpanel">
                    <x-material-card title="Kenaikan Kelas Massal" icon="stairs" color="purple" subtitle="Promosikan seluruh siswa dari satu tingkat ke tingkat yang lebih tinggi">
                        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
                            <div class="flex items-start">
                                <span class="material-icons mr-2 text-blue-600">info</span>
                                <div>
                                    <h5 class="font-bold">Panduan Kenaikan Kelas:</h5>
                                    <ul class="list-disc ml-5 mt-1 text-xs space-y-1">
                                        <li>Petakan setiap kelas dari tingkatan saat ini ke kelas baru di tingkatan yang lebih tinggi.</li>
                                        <li>Untuk tingkatan paling tinggi (misalnya kelas XII), pilih opsi <strong>"Lulus (Alumni - Arsipkan)"</strong> untuk meluluskan siswa. Siswa yang lulus akan diarsipkan (soft delete) sehingga tidak aktif di sistem namun riwayat absensi lama mereka aman.</li>
                                        <li>Proses kenaikan kelas ini sebaiknya dijalankan sebelum melakukan pembersihan data absensi, agar siswa lama terkelompokkan dengan benar terlebih dahulu.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('academic.year.promote') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memproses kenaikan kelas masal sesuai dengan pemetaan ini?')">
                            @csrf
                            
                            <div class="space-y-6">
                                @forelse($levels as $level)
                                    @if($level->classes->count() > 0)
                                        @php
                                            $mapping = $levelMapping[$level->id] ?? null;
                                            $nextLevel = $mapping['next'] ?? null;
                                            $nextClasses = $mapping['next_classes'] ?? collect();
                                        @endphp
                                        
                                        <div class="p-4 border rounded-xl bg-gray-50">
                                            <div class="flex items-center justify-between border-b pb-2 mb-4">
                                                <h4 class="font-bold text-gray-800 flex items-center text-sm md:text-base">
                                                    <span class="bg-purple-100 text-purple-800 text-xs px-2.5 py-1 rounded-full mr-2 font-mono">Order {{ $level->level_order }}</span>
                                                    Tingkat: {{ $level->name }}
                                                </h4>
                                                @if($nextLevel)
                                                    <span class="text-xs text-gray-500 font-medium flex items-center">
                                                        Rekomendasi Naik ke: <strong>{{ $nextLevel->name }}</strong> (Order {{ $nextLevel->level_order }})
                                                    </span>
                                                @else
                                                    <span class="bg-red-100 text-red-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-red-200">
                                                        Tingkat Tertinggi
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="grid gap-4 md:grid-cols-2">
                                                @foreach($level->classes as $class)
                                                    <div class="bg-white p-3 rounded-lg border flex flex-col md:flex-row md:items-center justify-between shadow-sm">
                                                        <div class="mb-2 md:mb-0">
                                                            <span class="block font-bold text-gray-700 text-sm">{{ $class->name }}</span>
                                                            <span class="text-[10px] text-gray-400 font-bold uppercase">{{ $class->students()->count() }} Siswa</span>
                                                        </div>
                                                        
                                                        <div class="w-full md:w-3/5">
                                                            <select name="class_map[{{ $class->id }}]" class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2" required>
                                                                <option value="">-- Pilih Tujuan --</option>
                                                                
                                                                @if($nextLevel && $nextClasses->count() > 0)
                                                                    <optgroup label="Naik Kelas ke ({{ $nextLevel->name }})">
                                                                        @foreach($nextClasses as $nextClass)
                                                                            <option value="{{ $nextClass->id }}" 
                                                                                {{-- Auto-recommend by name matching if possible (e.g. X-A to XI-A) --}}
                                                                                @if(substr(strtolower($class->name), 1) === substr(strtolower($nextClass->name), 2) || substr(strtolower($class->name), 1) === substr(strtolower($nextClass->name), 3))
                                                                                    selected
                                                                                @endif
                                                                            >
                                                                                {{ $nextClass->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </optgroup>
                                                                @endif

                                                                <optgroup label="Tindakan Khusus">
                                                                    <option value="lulus" {{ !$nextLevel ? 'selected' : '' }}>Lulus (Alumni - Arsipkan)</option>
                                                                </optgroup>
                                                            </select>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-gray-500 italic text-center py-6">Belum ada data tingkat/kelas di sistem.</p>
                                @endforelse
                            </div>

                            @if($levels->count() > 0)
                                <div class="mt-8 flex justify-end">
                                    <button type="submit" class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-6 py-3 text-center flex items-center shadow-lg">
                                        <span class="material-icons mr-2">check_circle</span>
                                        Eksekusi Kenaikan Kelas Massal
                                    </button>
                                </div>
                            @endif
                        </form>
                    </x-material-card>
                </div>

                <!-- Tab 2: Backup & Bersihkan Absensi -->
                <div class="hidden space-y-6" id="cleanup-content" role="tabpanel">
                    <x-material-card title="Backup & Bersihkan Data Kehadiran" icon="settings_backup_restore" color="red" subtitle="Pengosongan database absensi secara aman menjelang tahun ajaran baru">
                        
                        <div class="p-5 border-l-4 border-red-600 bg-red-50 text-red-900 rounded-r-lg mb-6">
                            <div class="flex items-start">
                                <span class="material-icons text-red-600 mr-2 text-2xl">warning</span>
                                <div>
                                    <h4 class="font-bold text-base text-red-800">PERINGATAN KERAS & PENTING!</h4>
                                    <p class="mt-1 text-sm leading-relaxed">
                                        Proses ini akan **MENGHAPUS SELAMANYA** data berikut dari database aktif Anda:
                                    </p>
                                    <ul class="list-disc ml-5 mt-1 text-xs space-y-1 font-semibold text-red-700">
                                        <li>Seluruh Riwayat Kehadiran Siswa (`attendance_students`)</li>
                                        <li>Seluruh Riwayat Kehadiran Guru (`attendance_teachers`)</li>
                                        <li>Seluruh Riwayat Buku Tamu (`guests`)</li>
                                        <li>Seluruh Riwayat Pengiriman Log WhatsApp (`wa_logs`)</li>
                                    </ul>
                                    <p class="mt-3 text-xs leading-relaxed text-red-800">
                                        Sebelum data tersebut dihapus, sistem akan **otomatis membuat backup cadangan database penuh** (format `.sql`) dan menyimpan file tersebut secara aman di server. Anda dapat mengunduh backup tersebut setelah pembersihan selesai.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('academic.year.cleanup') }}" method="POST" id="cleanup-form" class="space-y-6">
                            @csrf

                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-4">
                                <div class="flex items-center">
                                    <input type="checkbox" id="confirm_checkbox" name="confirm_checkbox" required
                                        class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                                    <label for="confirm_checkbox" class="ml-2 text-sm font-medium text-gray-700 cursor-pointer">
                                        Saya sadar dan menyetujui penghapusan data riwayat absensi, tamu, dan log WA di atas secara permanen.
                                    </label>
                                </div>

                                <div>
                                    <label for="confirm_text" class="block mb-1 text-xs font-bold text-gray-700 uppercase">
                                        Ketik Kata Kunci Konfirmasi
                                    </label>
                                    <input type="text" id="confirm_text" name="confirm_text" required autocomplete="off"
                                        placeholder="Ketik: BERSIHKAN DATABASE ABSENSI"
                                        class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5">
                                    <p class="mt-1 text-xs text-gray-400">Harap ketik frasa di atas dengan huruf kapital secara tepat untuk mengaktifkan tombol pembersihan.</p>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" id="cleanup-submit-btn" disabled
                                    class="text-white bg-gray-400 cursor-not-allowed font-medium rounded-lg text-sm px-6 py-3 text-center flex items-center shadow">
                                    <span class="material-icons mr-2">delete_forever</span>
                                    Buat Backup & Bersihkan Database
                                </button>
                            </div>
                        </form>
                    </x-material-card>
                </div>
            </div>

            <!-- Card 3: Riwayat Backup Database & Buku Tamu (Selalu Tampil) -->
            <x-material-card title="Riwayat Backup Database & Buku Tamu" icon="cloud_download" color="gray" subtitle="Daftar berkas cadangan database yang tersimpan di server">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Waktu Backup</th>
                                <th scope="col" class="px-6 py-3">Nama File Backup</th>
                                <th scope="col" class="px-6 py-3">Jenis</th>
                                <th scope="col" class="px-6 py-3">Ukuran File</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($backups as $backup)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono">
                                        {{ $backup['date'] }}
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900 break-all text-xs">
                                        {{ $backup['filename'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($backup['type'] === 'Full Backup')
                                            <span class="bg-blue-100 text-blue-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-blue-200">
                                                Full Backup
                                            </span>
                                        @else
                                            <span class="bg-green-100 text-green-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-green-200 font-mono">
                                                Buku Tamu
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-mono">
                                        {{ $backup['size'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center space-x-3">
                                            <a href="{{ route('academic.year.download', $backup['filename']) }}" 
                                                class="text-blue-600 hover:text-blue-800 flex items-center font-bold text-xs" 
                                                title="Unduh Berkas SQL">
                                                <span class="material-icons text-sm mr-1">download</span> Unduh
                                            </a>
                                            
                                            @if($backup['type'] === 'Buku Tamu')
                                                <button type="button" 
                                                    onclick="openRestoreModal('{{ $backup['filename'] }}')"
                                                    class="text-green-600 hover:text-green-800 flex items-center font-bold text-xs"
                                                    title="Restore Data Buku Tamu">
                                                    <span class="material-icons text-sm mr-1">settings_backup_restore</span> Restore Tamu
                                                </button>
                                            @endif

                                            <form action="{{ route('academic.year.delete-backup', $backup['filename']) }}" method="POST" 
                                                class="inline" onsubmit="return confirm('Hapus berkas backup ini dari server secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 flex items-center font-bold text-xs" title="Hapus Backup">
                                                    <span class="material-icons text-sm mr-1">delete</span> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">
                                        Belum ada riwayat berkas backup tersimpan di server.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-material-card>

        </div>
    </div>

    <!-- Restore Buku Tamu Modal -->
    <div id="restoreModal" tabindex="-1" aria-hidden="true" 
        class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center">
        <div class="relative w-full max-w-lg max-h-full">
            <div class="relative bg-white rounded-xl shadow-2xl border overflow-hidden">
                <form id="restore-form" method="POST">
                    @csrf
                    <!-- Header -->
                    <div class="flex items-start justify-between p-4 border-b bg-green-50">
                        <h3 class="text-lg font-bold text-green-900 flex items-center">
                            <span class="material-icons mr-2 text-green-600">settings_backup_restore</span> 
                            Restore Buku Tamu
                        </h3>
                        <button type="button" onclick="closeRestoreModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                            <span class="material-icons">close</span>
                        </button>
                    </div>
                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <div class="p-4 bg-yellow-50 text-yellow-800 border border-yellow-200 rounded-lg text-xs leading-relaxed">
                            <p class="font-bold mb-1">PENTING:</p>
                            Proses restore ini akan **MENGOSONGKAN** seluruh data buku tamu (`guests`) aktif saat ini dan menggantinya dengan data historis dari file backup terpilih.
                        </div>

                        <div>
                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">File Terpilih</span>
                            <span id="restore-file-display" class="text-xs font-mono font-bold text-gray-800 break-all bg-gray-50 border p-2 rounded block"></span>
                        </div>

                        <div>
                            <label for="restore_confirm_text" class="block mb-1 text-xs font-bold text-gray-700 uppercase">
                                Ketik Kata Kunci Konfirmasi
                            </label>
                            <input type="text" id="restore_confirm_text" name="restore_confirm_text" required autocomplete="off"
                                placeholder="Ketik: RESTORE TAMU"
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        </div>
                    </div>
                    <!-- Footer -->
                    <div class="flex items-center p-4 border-t bg-gray-50 justify-end space-x-2">
                        <button type="button" onclick="closeRestoreModal()" class="text-gray-700 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 font-medium rounded-lg text-sm px-5 py-2.5">Batal</button>
                        <button type="submit" id="restore-submit-btn" disabled
                            class="text-white bg-gray-400 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Eksekusi Restore Tamu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript to Handle Tabs and Form Warnings -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Tabs switching logic
            const tabButtons = document.querySelectorAll('#tabs button');
            const tabPanels = document.querySelectorAll('#tab-contents > div');

            tabButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Deactivate all buttons
                    tabButtons.forEach(btn => {
                        btn.classList.remove('text-blue-600', 'border-blue-600', 'active');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Activate clicked button
                    button.classList.add('text-blue-600', 'border-blue-600', 'active');
                    button.classList.remove('border-transparent', 'text-gray-500');

                    // Hide all panels
                    tabPanels.forEach(panel => {
                        panel.classList.add('hidden');
                    });

                    // Show target panel
                    const targetSelector = button.getAttribute('data-target');
                    const targetPanel = document.querySelector(targetSelector);
                    if (targetPanel) {
                        targetPanel.classList.remove('hidden');
                    }
                });
            });

            // Cleanup Form Submission validation
            const cleanupForm = document.getElementById('cleanup-form');
            const confirmCheckbox = document.getElementById('confirm_checkbox');
            const confirmText = document.getElementById('confirm_text');
            const cleanupSubmitBtn = document.getElementById('cleanup-submit-btn');

            function validateCleanupForm() {
                if (confirmCheckbox.checked && confirmText.value.trim() === 'BERSIHKAN DATABASE ABSENSI') {
                    cleanupSubmitBtn.disabled = false;
                    cleanupSubmitBtn.className = "text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-6 py-3 text-center flex items-center shadow-lg cursor-pointer";
                } else {
                    cleanupSubmitBtn.disabled = true;
                    cleanupSubmitBtn.className = "text-white bg-gray-400 cursor-not-allowed font-medium rounded-lg text-sm px-6 py-3 text-center flex items-center shadow";
                }
            }

            confirmCheckbox.addEventListener('change', validateCleanupForm);
            confirmText.addEventListener('input', validateCleanupForm);

            // Restore Modal validation logic
            const restoreConfirmText = document.getElementById('restore_confirm_text');
            const restoreSubmitBtn = document.getElementById('restore-submit-btn');

            restoreConfirmText.addEventListener('input', function () {
                if (restoreConfirmText.value.trim() === 'RESTORE TAMU') {
                    restoreSubmitBtn.disabled = false;
                    restoreSubmitBtn.className = "text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center cursor-pointer shadow";
                } else {
                    restoreSubmitBtn.disabled = true;
                    restoreSubmitBtn.className = "text-white bg-gray-400 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center";
                }
            });
        });

        // Restore Modal Functions
        function openRestoreModal(filename) {
            document.getElementById('restore-file-display').textContent = filename;
            
            // Set dynamic form action
            const form = document.getElementById('restore-form');
            form.action = `/academic-year/restore-guests/${filename}`;
            
            // Reset input and button state
            document.getElementById('restore_confirm_text').value = '';
            const btn = document.getElementById('restore-submit-btn');
            btn.disabled = true;
            btn.className = "text-white bg-gray-400 cursor-not-allowed font-medium rounded-lg text-sm px-5 py-2.5 text-center";

            document.getElementById('restoreModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeRestoreModal() {
            document.getElementById('restoreModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</x-app-layout>
