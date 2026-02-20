<x-app-layout>
    <x-slot name="title">Data Siswa</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Daftar Siswa" icon="people" color="purple">
                <x-slot name="actions">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('students.import') }}" class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 active:bg-orange-900 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <span class="material-icons text-sm mr-2">upload_file</span>
                            Import Excel
                        </a>
                        <a href="{{ route('students.create') }}" class="inline-flex items-center px-4 py-2 bg-navy-900 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-navy-800 active:bg-navy-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <span class="material-icons text-sm mr-2">add</span>
                            Tambah Siswa
                        </a>
                    </div>
                </x-slot>

                 <div class="mb-4">
                    <form action="{{ route('students.index') }}" method="GET" class="flex flex-col md:flex-row space-y-2 md:space-y-0 md:space-x-4">
                        <select name="level_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full md:w-1/4 p-2.5">
                            <option value="">Semua Jenjang</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                            @endforeach
                        </select>

                        <select name="class_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full md:w-1/4 p-2.5">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center">
                            <span class="material-icons text-sm mr-1">filter_list</span> Filter
                        </button>
                    </form>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-800">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Foto</th>
                                <th scope="col" class="px-6 py-3">NIS</th>
                                <th scope="col" class="px-6 py-3">Nama</th>
                                <th scope="col" class="px-6 py-3">Kelas</th>
                                <th scope="col" class="px-6 py-3">L/P</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    @if($student->photo)
                                        <img src="{{ asset('storage/photo/students/' . $student->photo) }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <span class="material-icons">person</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $student->nis }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $student->name }}</td>
                                <td class="px-6 py-4">{{ $student->classRoom->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $student->gender }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('students.show', $student->id) }}" class="text-blue-500 hover:text-blue-700" title="QR Code">
                                            <span class="material-icons">qr_code</span>
                                        </a>
                                        <a href="{{ route('students.edit', $student->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        <form action="{{ route('students.destroy', $student->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus siswa ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
