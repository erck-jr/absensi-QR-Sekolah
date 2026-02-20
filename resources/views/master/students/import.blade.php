<x-app-layout>
    <x-slot name="title">Import Siswa Bulk</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Siswa Massal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Import Siswa Massal" icon="upload_file" color="orange">
                <x-slot name="actions">
                    <a href="{{ route('students.import.template') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <span class="material-icons text-sm mr-2">download</span>
                        Template Excel
                    </a>
                </x-slot>

                @if (session('import_errors'))
                    <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg">
                        <div class="flex items-center mb-2">
                            <span class="material-icons text-red-500 mr-2">error</span>
                            <h4 class="text-red-800 font-bold">Import Gagal! Harap Perbaiki Kesalahan Berikut:</h4>
                        </div>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <p class="mt-4 text-xs text-red-600 font-medium italic">* Tidak ada data yang dimasukkan ke database karena terjadi kesalahan di atas (Rollback Aktif).</p>
                    </div>
                @endif

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Instructions -->
                    <div class="bg-gray-50 p-6 rounded-xl border border-gray-100 h-full">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <span class="material-icons text-blue-600 mr-2">info</span>
                            Aturan Pengisian Template
                        </h4>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start">
                                <span class="font-bold text-blue-600 mr-2">1.</span>
                                <span>Gunakan file template yang diunduh dari tombol di atas. Jangan mengubah struktur kolom.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-blue-600 mr-2">2.</span>
                                <span><strong>NIS</strong> harus unik dan berupa angka.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-blue-600 mr-2">3.</span>
                                <span><strong>Kelas</strong> harus dipilih menggunakan dropdown yang tersedia di file Excel.</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-blue-600 mr-2">4.</span>
                                <span><strong>Jenis Kelamin</strong> diisi L (Laki-laki) atau P (Perempuan).</span>
                            </li>
                            <li class="flex items-start">
                                <span class="font-bold text-blue-600 mr-2">5.</span>
                                <span>Foto tidak dapat diimport via Excel dan akan bernilai kosong.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Upload Form -->
                    <div class="border-2 border-dashed border-gray-200 p-8 rounded-xl flex flex-col justify-center items-center h-full">
                        <form action="{{ route('students.import.process') }}" method="POST" enctype="multipart/form-data" class="w-full">
                            @csrf
                            <div class="text-center mb-6">
                                <span class="material-icons text-5xl text-gray-300 mb-2">cloud_upload</span>
                                <h4 class="font-medium text-gray-700">Upload File Anda</h4>
                                <p class="text-xs text-gray-400">Format yang didukung: .xlsx, .xls</p>
                            </div>
                            
                            <div class="mb-6">
                                <input type="file" name="file" id="file" class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-navy-50 file:text-navy-700
                                    hover:file:bg-navy-100 cursor-pointer" 
                                    required accept=".xlsx, .xls">
                            </div>

                            <button type="submit" class="w-full py-3 bg-navy-900 text-white rounded-lg font-bold hover:bg-navy-800 transition-colors flex justify-center items-center shadow-lg">
                                <span class="material-icons mr-2">upload_file</span>
                                Mulai Proses Import
                            </button>
                            
                            <div class="mt-4 text-center">
                                <a href="{{ route('students.index') }}" class="text-sm text-gray-500 hover:text-navy-700 transition-colors">Batal dan Kembali</a>
                            </div>
                        </form>
                    </div>
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
