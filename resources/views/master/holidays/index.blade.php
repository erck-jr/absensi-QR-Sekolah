<x-app-layout>
    <x-slot name="title">Data Libur</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Libur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Data Libur" icon="event" color="blue">
                <x-slot name="actions">
                    <a href="{{ route('holidays.create') }}" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Hari Libur</a>
                </x-slot>
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50  ">
                            <tr>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Keterangan</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($holidays as $holiday)
                            <tr class="bg-white border-b  ">
                                <td class="px-6 py-4">
                                    @if($holiday->start_date == $holiday->end_date)
                                        {{ \Carbon\Carbon::parse($holiday->start_date)->format('d F Y') }}
                                    @else
                                        {{ \Carbon\Carbon::parse($holiday->start_date)->format('d F Y') }} - {{ \Carbon\Carbon::parse($holiday->end_date)->format('d F Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4">{{ $holiday->info }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('holidays.edit', $holiday->id) }}" class="font-medium text-yellow-600  hover:underline">Edit</a>
                                    <form action="{{ route('holidays.destroy', $holiday->id) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Hapus data libur ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600  hover:underline">Hapus</button>
                                    </form>
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

