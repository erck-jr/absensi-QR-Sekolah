<x-app-layout>
    <x-slot name="title">Generate ID Card</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generate ID Card') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Student Generator -->
            <x-material-card title="Generate Kartu Siswa" icon="badge" color="indigo">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="class_id" class="block mb-2 text-sm font-medium text-gray-900 ">Pilih Kelas (Opsional)</label>
                        <select id="class_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button onclick="startStudentGeneration()" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center focus:outline-none">
                            <span class="material-icons text-sm mr-2">play_arrow</span> Generate Kartu Siswa
                        </button>
                        <button onclick="downloadZip('student')" class="text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center focus:outline-none">
                            <span class="material-icons text-sm mr-2">download</span> Download ZIP
                        </button>
                    </div>
                </div>
            </x-material-card>

            <!-- Teacher Generator -->
            <x-material-card title="Generate Kartu Guru" icon="card_membership" color="green">
                 <p class="mb-4 text-sm text-gray-600">Generate kartu ID untuk semua guru yang terdaftar.</p>
                  <div class="flex space-x-2">
                    <button onclick="startTeacherGeneration()" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">play_circle_filled</span> Generate Semua Kartu Guru
                    </button>
                    <button onclick="downloadZip('teacher')" class="text-white bg-orange-600 hover:bg-orange-700 focus:ring-4 focus:ring-orange-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center focus:outline-none">
                        <span class="material-icons text-sm mr-2">download</span> Download ZIP
                    </button>
                  </div>
            </x-material-card>

        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function startStudentGeneration() {
            const classId = document.getElementById('class_id').value;
            
            // 1. Get IDs
            Swal.fire({ title: 'Mengambil data siswa...', didOpen: () => Swal.showLoading() });
            
            try {
                const response = await fetch(`{{ route('generator.get-students') }}?class_id=${classId}`);
                const ids = await response.json();
                
                if (ids.length === 0) {
                    Swal.fire('Info', 'Tidak ada data siswa ditemukan.', 'info');
                    return;
                }

                processQueue(ids, 'student');

            } catch (error) {
                Swal.fire('Error', 'Gagal mengambil data siswa.', 'error');
            }
        }

        async function startTeacherGeneration() {
             // 1. Get IDs
            Swal.fire({ title: 'Mengambil data guru...', didOpen: () => Swal.showLoading() });
            
            try {
                const response = await fetch(`{{ route('generator.get-teachers') }}`);
                const ids = await response.json();
                
                if (ids.length === 0) {
                    Swal.fire('Info', 'Tidak ada data guru ditemukan.', 'info');
                    return;
                }

                processQueue(ids, 'teacher');

            } catch (error) {
                Swal.fire('Error', 'Gagal mengambil data guru.', 'error');
            }
        }

        async function processQueue(ids, type) {
            let processed = 0;
            const total = ids.length;
            const url = type === 'student' ? `{{ route('generator.student') }}` : `{{ route('generator.teacher') }}`;
            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            Swal.fire({
                title: `Generating ${type === 'student' ? 'Siswa' : 'Guru'} ID Cards`,
                html: `Progress: <b>0</b>/${total}`,
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            for (const id of ids) {
                try {
                    await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({ id: id })
                    });
                    
                    processed++;
                    Swal.update({ html: `Progress: <b>${processed}</b>/${total}` });

                } catch (error) {
                    console.error('Generation failed for ID:', id);
                }
            }

            Swal.fire('Selesai!', `Berhasil generate ${processed} kartu dari ${total} data.`, 'success');
        }

        function downloadZip(type) {
            const classId = type === 'student' ? document.getElementById('class_id').value : '';
            const url = `{{ route('generator.download-zip') }}?type=${type}&class_id=${classId}`;
            window.location.href = url;
        }
    </script>
    @endpush
</x-app-layout>

