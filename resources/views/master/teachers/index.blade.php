<x-app-layout>
    <x-slot name="title">Data Guru</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Guru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Daftar Guru" icon="school" color="green">
                <x-slot name="actions">
                    <a href="{{ route('teachers.create') }}" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Guru
                    </a>
                </x-slot>

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Foto</th>
                                <th scope="col" class="px-6 py-3">NUPTK</th>
                                <th scope="col" class="px-6 py-3">Nama</th>
                                <th scope="col" class="px-6 py-3">L/P</th>
                                <th scope="col" class="px-6 py-3">No. HP</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($teachers as $teacher)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    @if($teacher->photo)
                                        <img src="{{ asset('storage/photo/teachers/' . $teacher->photo) }}" alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                            <span class="material-icons">person</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $teacher->nuptk }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $teacher->name }}</td>
                                <td class="px-6 py-4">{{ $teacher->gender }}</td>
                                <td class="px-6 py-4">{{ $teacher->phone }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('teachers.show', $teacher->id) }}" class="text-blue-500 hover:text-blue-700" title="QR Code">
                                            <span class="material-icons">qr_code</span>
                                        </a>
                                        <a href="{{ route('teachers.edit', $teacher->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus guru ini?')">
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
