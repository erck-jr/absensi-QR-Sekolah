<x-app-layout>
    <x-slot name="title">Edit WA Gateway</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit WhatsApp Gateway') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Edit Gateway" icon="edit" color="green">
                <form action="{{ route('wagateways.update', $wagateway->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-6">
                        <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Gateway</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $wagateway->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    <div class="mb-6">
                        <label for="api_url" class="block mb-2 text-sm font-medium text-gray-900">API URL</label>
                        <input type="url" id="api_url" name="api_url" value="{{ old('api_url', $wagateway->api_url) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    <div class="mb-6">
                        <label for="api_token" class="block mb-2 text-sm font-medium text-gray-900">API Token</label>
                        <input type="text" id="api_token" name="api_token" value="{{ old('api_token', $wagateway->api_token) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    </div>
                    <div class="flex items-start mb-6">
                        <div class="flex items-center h-5">
                            <input id="is_active" name="is_active" type="checkbox" value="1" class="w-4 h-4 border border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-green-300" {{ $wagateway->is_active ? 'checked' : '' }}>
                        </div>
                        <label for="is_active" class="ml-2 text-sm font-medium text-gray-900">Aktif</label>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Simpan Perubahan
                        </button>
                        <a href="{{ route('wagateways.index') }}" class="text-gray-700 bg-white border border-gray-300 focus:ring-4 focus:outline-none focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 hover:bg-gray-100 focus:z-10 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700">Batal</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

