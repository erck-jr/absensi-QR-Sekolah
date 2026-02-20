<x-app-layout>
    <x-slot name="title">Edit Libur</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Hari Libur') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Edit Hari Libur" icon="edit" color="red">
                <form action="{{ route('holidays.update', $holiday->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="start_date" class="block mb-2 text-sm font-medium text-gray-900">Dari Tanggal</label>
                            <input type="date" id="start_date" name="start_date" value="{{ old('start_date', isset($group) ? $group->start_date : $holiday->dates->format('Y-m-d')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="end_date" class="block mb-2 text-sm font-medium text-gray-900">Sampai Tanggal</label>
                            <input type="date" id="end_date" name="end_date" value="{{ old('end_date', isset($group) ? $group->end_date : $holiday->dates->format('Y-m-d')) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5" required>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="info" class="block mb-2 text-sm font-medium text-gray-900">Keterangan</label>
                        <textarea id="info" name="info" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-red-500 focus:border-red-500" required>{{ old('info', $holiday->info) }}</textarea>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan Perubahan
                        </button>
                        <a href="{{ route('holidays.index') }}" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-100 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Batal</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

