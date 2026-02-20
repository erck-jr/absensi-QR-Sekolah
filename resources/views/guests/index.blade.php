<x-app-layout>
    <x-slot name="title">Buku Tamu</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buku Tamu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Buku Tamu" icon="menu_book" color="blue">
                <x-slot name="actions">
                    <button data-modal-target="add-guest-modal" data-modal-toggle="add-guest-modal" class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow" type="button">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Tamu
                    </button>
                </x-slot>

                <div class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
                    <form action="{{ route('guests.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                        <div>
                            <label for="date" class="block mb-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">Filter Tanggal</label>
                            <input type="date" name="date" id="date" value="{{ request('date') }}" 
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex items-center">
                                <span class="material-icons text-sm mr-1">filter_alt</span> Filter
                            </button>
                            @if(request('date'))
                                <a href="{{ route('guests.index') }}" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center">
                                    <span class="material-icons text-sm mr-1">restart_alt</span> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama Tamu</th>
                                <th scope="col" class="px-6 py-3">Asal / Instansi</th>
                                <th scope="col" class="px-6 py-3">Menemui</th>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Oleh (Waktu)</th>
                                <th scope="col" class="px-6 py-3 text-center">Status / Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($guests as $guest)
                                <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                    {{ $guest->name }}
                                </td>
                                <td class="px-6 py-4">{{ $guest->origin }}</td>
                                <td class="px-6 py-4 text-blue-600 font-medium">{{ $guest->meet_with }}</td>
                                <td class="px-6 py-4">{{ $guest->check_in->format('d/m/Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-gray-400">Keperluan:</div>
                                    <div class="text-sm italic mb-1">{{ $guest->necessity }}</div>
                                    <div class="text-xs font-medium bg-gray-100 rounded px-1 w-fit">In: {{ $guest->check_in->format('H:i') }} | Out: {{ $guest->check_out ? $guest->check_out->format('H:i') : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if(!$guest->check_out)
                                    <form action="{{ route('guests.update', $guest->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium flex items-center justify-center">
                                            <span class="material-icons text-sm mr-1">logout</span> Checkout
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-green-600 font-medium flex items-center justify-center">
                                        <span class="material-icons text-sm mr-1">check_circle</span> Selesai
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-material-card>
        </div>
    </div>

    <!-- Add Guest Modal -->
    <div id="add-guest-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-md max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20">
                <button type="button" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center  " data-modal-hide="add-guest-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="px-6 py-6 lg:px-8">
                    <h3 class="mb-4 text-xl font-medium text-gray-900 ">Catat Tamu Baru</h3>
                    <form class="space-y-6" action="{{ route('guests.store') }}" method="POST">
                        @csrf
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 ">Nama</label>
                            <input type="text" name="name" id="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    " required>
                        </div>
                        <div>
                            <label for="origin" class="block mb-2 text-sm font-medium text-gray-900 ">Asal / Instansi</label>
                            <input type="text" name="origin" id="origin" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                        </div>
                        <div>
                            <label for="meet_with" class="block mb-2 text-sm font-medium text-gray-900 ">Menemui</label>
                            <input type="text" name="meet_with" id="meet_with" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                        </div>
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 ">No. HP (Opsional)</label>
                            <input type="text" name="phone" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    ">
                        </div>
                        <div>
                            <label for="necessity" class="block mb-2 text-sm font-medium text-gray-900 ">Keperluan</label>
                            <textarea name="necessity" id="necessity" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5    " required></textarea>
                        </div>
                        <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center   ">Simpan</button>
                    </form>
                </div>
            </div>
        </div>
    </div> 

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== "undefined") {
                const dataTable = new simpleDatatables.DataTable("#search-table", {
                    searchable: true,
                    fixedHeight: false,
                    perPage: 10,
                    labels: {
                        placeholder: "Cari tamu...",
                        searchTitle: "Cari dalam tabel",
                        perPage: "entri per halaman",
                        noRows: "Tidak ada data tamu ditemukan",
                        info: "Menampilkan {start} sampai {end} dari {rows} entri",
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>

