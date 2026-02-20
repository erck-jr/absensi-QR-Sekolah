<x-app-layout>
    <x-slot name="title">Data Shift</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Shift') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Data Shift" icon="access_time" color="blue">
                <x-slot name="actions">
                    <a href="{{ route('shifts.create') }}" class="text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Shift
                    </a>
                </x-slot>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                        <table id="search-table" class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50  ">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Shift</th>
                                    <th scope="col" class="px-6 py-3">Jam Masuk</th>
                                    <th scope="col" class="px-6 py-3">Jam Pulang</th>
                                    <th scope="col" class="px-6 py-3">Toleransi (Menit)</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shifts as $shift)
                                <tr class="bg-white border-b  ">
                                    <td class="px-6 py-4">{{ $shift->name }}</td>
                                    <td class="px-6 py-4">{{ $shift->check_in_time }}</td>
                                    <td class="px-6 py-4">{{ $shift->check_out_time }}</td>
                                    <td class="px-6 py-4">{{ $shift->late_check_in_minute }}</td>
                                    <td class="px-6 py-4">
                                        <form action="{{ route('shifts.toggle', $shift->id) }}" method="POST">
                                            @csrf
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" value="" class="sr-only peer" onchange="this.form.submit()" {{ $shift->is_active ? 'checked disabled' : '' }}>
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300  rounded-full peer  peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all  peer-checked:bg-blue-600"></div>
                                                <span class="ml-3 text-sm font-medium text-gray-900 ">{{ $shift->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('shifts.edit', $shift->id) }}" class="font-medium text-yellow-600  hover:underline">Edit</a>
                                        <form action="{{ route('shifts.destroy', $shift->id) }}" method="POST" class="inline ml-2" onsubmit="return confirm('Hapus shift ini?')">
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
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

