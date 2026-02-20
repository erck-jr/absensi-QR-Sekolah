<x-app-layout>
    <x-slot name="title">Data Tingkat</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Tingkat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Data Tingkat" icon="stairs" color="indigo">
                <x-slot name="actions">
                    <a href="{{ route('levels.create') }}" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Tingkat
                    </a>
                </x-slot>

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Tingkat</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($levels as $level)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $level->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('levels.edit', $level->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        <form action="{{ route('levels.destroy', $level->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus tingkat ini?')">
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
