<x-app-layout>
    <x-slot name="title">Tambah Siswa</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Tambah Siswa Baru" icon="person_add" color="blue">
                <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="nis" class="block mb-2 text-sm font-medium text-gray-900">NIS</label>
                            <input type="text" id="nis" name="nis" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="class_id" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                            <select id="class_id" name="class_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @foreach($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="gender" class="block mb-2 text-sm font-medium text-gray-900">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">No. HP (Opsional)</label>
                            <input type="text" id="phone" name="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div>
                            <label for="photo" class="block mb-2 text-sm font-medium text-gray-900">Foto (Opsional)</label>
                            <input type="file" id="photo" name="photo" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                            <p class="mt-1 text-sm text-gray-500" id="file_input_help">JPG, PNG, GIF (Max. 2MB).</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan
                        </button>
                        <a href="{{ route('students.index') }}" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-100 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Batal</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

