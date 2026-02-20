<x-app-layout>
    <x-slot name="title">Absensi QR Scanner</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QR Scanner Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="QR Scanner Absensi" icon="qr_code_scanner" color="purple">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <!-- Left: Scanner -->
                    <div>
                        <div class="mb-4">
                            <label for="shift_id" class="block mb-2 text-sm font-medium text-gray-900">Pilih Shift</label>
                            <select id="shift_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5">
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->check_in_time }} - {{ $shift->check_out_time }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Mode Absensi</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" id="mode-in" data-mode="in" class="mode-btn flex items-center justify-center p-3 rounded-lg border-2 border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
                                    <span class="material-icons mr-2">login</span> Masuk
                                </button>
                                <button type="button" id="mode-out" data-mode="out" class="mode-btn flex items-center justify-center p-3 rounded-lg border-2 border-orange-200 bg-white text-orange-700 hover:bg-orange-50 transition-all duration-200">
                                    <span class="material-icons mr-2">logout</span> Pulang
                                </button>
                            </div>
                            <input type="hidden" id="scan_mode" value="in">
                        </div>

                        <div id="reader" width="100%" class="rounded-lg overflow-hidden border-2 border-dashed border-gray-300 mb-4"></div>

                        <!-- Manual Input Form -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label for="manual_code" class="block mb-2 text-sm font-medium text-gray-900">Input Manual (Jika Lensa Bermasalah)</label>
                            <div class="flex gap-2">
                                <input type="text" id="manual_code" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5" placeholder="Masukkan Kode Unik" autocomplete="off">
                                <button type="button" id="btn-manual-submit" class="text-white bg-purple-600 hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none">Submit</button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Result -->
                    <div>
                        <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Hasil Scan Terakhir</h3>
                        
                        <div id="result-card" class="hidden p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <h5 id="res-name" class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Nama Siswa</h5>
                            <p id="res-time" class="mb-3 font-normal text-gray-700 flex items-center">
                                <span class="material-icons text-sm mr-1">schedule</span> Waktu: 07:00:00
                            </p>
                            <span id="res-status" class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded">Tepat Waktu</span>
                        </div>

                        <div id="error-alert" class="hidden p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                            <div class="flex items-center">
                                <span class="material-icons mr-2">error</span>
                                <div>
                                    <span class="font-medium">Error!</span> <span id="error-message"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div id="success-alert" class="hidden p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                            <div class="flex items-center">
                                <span class="material-icons mr-2">check_circle</span>
                                <div>
                                    <span class="font-medium">Sukses!</span> <span id="success-text"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </x-material-card>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const shiftDropdown = document.getElementById('shift_id');
        const modeHidden = document.getElementById('scan_mode');
        const modeButtons = document.querySelectorAll('.mode-btn');

        // Mode Toggling Logic
        modeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.getAttribute('data-mode');
                setScanMode(mode);
            });
        });

        function setScanMode(mode) {
            modeHidden.value = mode;
            localStorage.setItem('last_scan_mode', mode);
            
            modeButtons.forEach(btn => {
                const btnMode = btn.getAttribute('data-mode');
                if (btnMode === mode) {
                    if (mode === 'in') {
                        btn.classList.remove('border-emerald-200', 'text-emerald-700', 'bg-white');
                        btn.classList.add('border-emerald-600', 'bg-emerald-600', 'text-white');
                    } else {
                        btn.classList.remove('border-orange-200', 'text-orange-700', 'bg-white');
                        btn.classList.add('border-orange-600', 'bg-orange-600', 'text-white');
                    }
                } else {
                    if (btnMode === 'in') {
                        btn.classList.add('border-emerald-200', 'text-emerald-700', 'bg-white');
                        btn.classList.remove('border-emerald-600', 'bg-emerald-600', 'text-white');
                    } else {
                        btn.classList.add('border-orange-200', 'text-orange-700', 'bg-white');
                        btn.classList.remove('border-orange-600', 'bg-orange-600', 'text-white');
                    }
                }
            });
        }

        // Restore shift and mode selection from localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const savedShiftId = localStorage.getItem('last_active_shift_id');
            if (savedShiftId) {
                shiftDropdown.value = savedShiftId;
            }

            const savedMode = localStorage.getItem('last_scan_mode') || 'in';
            setScanMode(savedMode);
        });

        // Save shift selection to localStorage
        shiftDropdown.addEventListener('change', function() {
            localStorage.setItem('last_active_shift_id', this.value);
        });

        // Manual Input Logic
        const manualCodeInput = document.getElementById('manual_code');
        const manualSubmitBtn = document.getElementById('btn-manual-submit');

        manualSubmitBtn.addEventListener('click', function() {
            const code = manualCodeInput.value.trim();
            if (code) {
                processScan(code);
                manualCodeInput.value = ''; // clear input after submit
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan',
                        text: 'Silakan masukkan kode unik terlebih dahulu!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('Silakan masukkan kode unik terlebih dahulu!');
                }
            }
        });

        manualCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                manualSubmitBtn.click();
            }
        });

        function processScan(decodedText) {
            // Prevent multiple scans
            if (window.isScanning) return;
            window.isScanning = true;

            // Audio Feedback
            let audio = new Audio('https://www.soundjay.com/buttons/beep-01a.mp3'); // Simple beep
            audio.play().catch(e => console.log('Audio playback blocked'));

            const shiftId = shiftDropdown.value;
            const scanMode = modeHidden.value;

            fetch("{{ route('scanner.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({
                    unique_code: decodedText,
                    shift_id: shiftId,
                    mode: scanMode
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success' || data.status === 'warning') {
                    showResult(data.data, data.status, data.message);
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError("Terjadi kesalahan koneksi.");
            })
            .finally(() => {
                setTimeout(() => { window.isScanning = false; }, 2000); // 2 seconds cooldown
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            processScan(decodedText);
        }

        function onScanFailure(error) {
            // handle scan failure, usually better to ignore and keep scanning.
            // console.warn(`Code scan error = ${error}`);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader",
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false);
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        function showResult(data, status, message) {
            document.getElementById('error-alert').classList.add('hidden');
            
            if (data) {
                document.getElementById('result-card').classList.remove('hidden');
                document.getElementById('res-name').innerText = data.name;
                document.getElementById('res-time').innerText = 'Waktu: ' + data.time;
                document.getElementById('res-status').innerText = data.status;
                
                // Color updates based on status
                const statusBadge = document.getElementById('res-status');
                if (data.status === 'Terlambat' || data.status === 'Pulang Cepat') {
                    statusBadge.className = "bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded";
                } else if (data.status === 'Tepat Waktu' || data.status === 'Pulang Normal') {
                    statusBadge.className = "bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded";
                } else {
                    statusBadge.className = "bg-yellow-100 text-yellow-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded";
                }
            } else {
                 document.getElementById('result-card').classList.add('hidden');
            }

            const successAlert = document.getElementById('success-alert');
            document.getElementById('success-text').innerText = message;
            successAlert.classList.remove('hidden');
            
            // Auto hide success after 3s
            setTimeout(() => {
                successAlert.classList.add('hidden');
            }, 3000);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: status === 'warning' ? 'warning' : 'success',
                    title: status === 'warning' ? 'Perhatian' : 'Berhasil',
                    text: message,
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        }

        function showError(msg) {
            document.getElementById('result-card').classList.add('hidden');
            document.getElementById('success-alert').classList.add('hidden');
            const errAlert = document.getElementById('error-alert');
            document.getElementById('error-message').innerText = msg;
            errAlert.classList.remove('hidden');
             setTimeout(() => {
                errAlert.classList.add('hidden');
            }, 3000);

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: msg,
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        }
    </script>
    @endpush
</x-app-layout>
