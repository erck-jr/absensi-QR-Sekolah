<x-app-layout>
    <x-slot name="title">Edit Shift</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Shift') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Edit Shift" icon="edit_calendar" color="blue">
                <form action="{{ route('shifts.update', $shift->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Shift</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $shift->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="late_check_in_minute" class="block mb-2 text-sm font-medium text-gray-900">Denda Telat (Menit)</label>
                            <input type="number" id="late_check_in_minute" name="late_check_in_minute" value="{{ old('late_check_in_minute', $shift->late_check_in_minute) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="check_in_time" class="block mb-2 text-sm font-medium text-gray-900">Jam Masuk</label>
                            <input type="time" id="check_in_time" name="check_in_time" value="{{ old('check_in_time', $shift->check_in_time) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label for="check_out_time" class="block mb-2 text-sm font-medium text-gray-900">Jam Pulang</label>
                            <input type="time" id="check_out_time" name="check_out_time" value="{{ old('check_out_time', $shift->check_out_time) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan Perubahan
                        </button>
                        <a href="{{ route('shifts.index') }}" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-100 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Batal</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

