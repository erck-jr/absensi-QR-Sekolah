<x-guest-layout>
    <x-slot name="title">Cek Kehadiran</x-slot>
    <div class="mb-8 text-center lg:text-left">
        <h2 class="text-3xl font-bold text-navy-900">Cek Kehadiran</h2>
        <p class="text-gray-500 mt-2">Cari status kehadiran siswa secara cepat</p>
    </div>

    <form id="attendance-form" class="space-y-6">
        @csrf
        <div>
            <x-input-label for="nis" :value="__('Nomor Induk Siswa (NIS)')" class="text-navy-700 font-semibold" />
            <x-text-input id="nis" class="block mt-1 w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl" type="text" name="nis" required autofocus placeholder="Contoh: 123456" />
        </div>

        <div>
            <x-input-label for="date" :value="__('Tanggal Absensi')" class="text-navy-700 font-semibold" />
            <x-text-input id="date" class="block mt-1 w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl" type="date" name="date" :value="date('Y-m-d')" required />
        </div>

        <div class="pt-2">
            <button type="submit" id="submit-btn" class="w-full flex justify-center items-center px-6 py-4 bg-orange-500 border border-transparent rounded-xl font-bold text-white uppercase tracking-widest hover:bg-orange-600 focus:bg-orange-600 active:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-lg shadow-orange-500/30">
                <span id="btn-text" class="flex items-center gap-2">
                    <span class="material-icons">search</span> Cek Sekarang
                </span>
                <span id="btn-loader" class="hidden ml-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>

        <div class="text-center mt-6">
            <a href="{{ route('welcome') }}" class="text-sm text-gray-500 hover:text-navy-900 transition-colors">
                <span class="material-icons text-xs align-middle">arrow_back</span> Kembali ke Beranda
            </a>
        </div>
    </form>

    <div id="result-box" class="mt-8 hidden">
        <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm">
            <h3 class="text-lg font-semibold text-gray-800 border-b pb-3 mb-4">Informasi Kehadiran</h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama Siswa</p>
                    <p id="res-name" class="font-medium text-gray-900">-</p>
                </div>
                <div>
                    <p class="text-gray-500">Kelas</p>
                    <p id="res-class" class="font-medium text-gray-900">-</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal</p>
                    <p id="res-date" class="font-medium text-gray-900">-</p>
                </div>
                <div>
                    <p class="text-gray-500">Status</p>
                    <p id="res-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">-</p>
                </div>
                <div>
                    <p class="text-gray-500">Jam Masuk</p>
                    <p id="res-in" class="font-medium text-gray-900">-</p>
                </div>
                <div>
                    <p class="text-gray-500">Jam Pulang</p>
                    <p id="res-out" class="font-medium text-gray-900">-</p>
                </div>
                <div class="col-span-2">
                    <p class="text-gray-500">Keterangan</p>
                    <p id="res-note" class="font-medium text-gray-900 italic">-</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('attendance-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const submitBtn = document.getElementById('submit-btn');
            const btnText = document.getElementById('btn-text');
            const btnLoader = document.getElementById('btn-loader');
            const resultBox = document.getElementById('result-box');
            
            // UI Loading state
            submitBtn.disabled = true;
            btnText.innerText = 'Memproses...';
            btnLoader.classList.remove('hidden');
            resultBox.classList.add('hidden');

            const formData = new FormData(form);

            fetch("{{ route('public.attendance.check') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const res = data.data;
                    document.getElementById('res-name').innerText = res.student_name;
                    document.getElementById('res-class').innerText = res.class_name;
                    document.getElementById('res-date').innerText = res.date;
                    
                    const statusEl = document.getElementById('res-status');
                    statusEl.innerText = res.status;
                    
                    // Style status
                    statusEl.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ';
                    if (res.status === 'Hadir') {
                        statusEl.classList.add('bg-green-100', 'text-green-800');
                    } else if (res.status === 'Alpa' || res.status === 'Belum Tersedia') {
                        statusEl.classList.add('bg-red-100', 'text-red-800');
                    } else if (res.status === 'Belum Absensi') {
                        statusEl.classList.add('bg-blue-100', 'text-blue-800');
                    } else {
                        statusEl.classList.add('bg-yellow-100', 'text-yellow-800');
                    }

                    document.getElementById('res-in').innerText = res.check_in;
                    document.getElementById('res-out').innerText = res.check_out;
                    document.getElementById('res-note').innerText = res.note;
                    
                    resultBox.classList.remove('hidden');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        confirmButtonColor: '#3085d6',
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem. Silakan coba lagi nanti.',
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                btnText.innerText = 'Cek Sekarang';
                btnLoader.classList.add('hidden');
            });
        });
    </script>
</x-guest-layout>
