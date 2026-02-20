<x-app-layout>
    <x-slot name="title">Edit Tingkat</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Tingkat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Edit Tingkat" icon="edit" color="indigo">
                <form action="{{ route('levels.update', $level->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Tingkat</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $level->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-indigo-700 hover:bg-indigo-800 focus:ring-4 focus:outline-none focus:ring-indigo-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan Perubahan
                        </button>
                        <a href="{{ route('levels.index') }}" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-100 focus:z-10">Batal</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

