<x-app-layout>
    <x-slot name="title">Edit User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Edit User" icon="manage_accounts" color="gray">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <div>
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Nama Lengkap</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5" required>
                            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5" required>
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="role" class="block mb-2 text-sm font-medium text-gray-900">Role</label>
                            <select id="role" name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5" required>
                                <option value="operator" {{ $user->role == 'operator' ? 'selected' : '' }}>Operator</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-gray-50">
                        <h4 class="text-md font-medium text-gray-900 mb-2">Ganti Password (Opsional)</h4>
                        <p class="text-xs text-gray-500 mb-4">Biarkan kosong jika tidak ingin mengganti password.</p>
                        
                        <div class="mb-4">
                            <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Password Baru</label>
                            <input type="password" id="password" name="password" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5">
                            @error('password') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Password Baru</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5">
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button type="submit" class="text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center flex items-center">
                            <span class="material-icons text-sm mr-2">save</span> Update User
                        </button>
                        <a href="{{ route('users.index') }}" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2 ml-2">Cancel</a>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
