<x-app-layout>
    <x-slot name="title">Manajemen User</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen User') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Daftar Pengguna" icon="manage_accounts" color="red">
                <x-slot name="actions">
                    <a href="{{ route('users.create') }}" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah User
                    </a>
                </x-slot>

                @if(session('success'))
                    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center" role="alert">
                        <span class="material-icons mr-2 text-green-600">check_circle</span>
                        <div>
                            <span class="font-medium">Success!</span> {{ session('success') }}
                        </div>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 flex items-center" role="alert">
                        <span class="material-icons mr-2 text-red-600">error</span>
                        <div>
                            <span class="font-medium">Error!</span> {{ session('error') }}
                        </div>
                    </div>
                @endif

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3 text-center">Role</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="bg-gray-200 rounded-full p-1 mr-2">
                                            <span class="material-icons text-gray-500" style="font-size: 20px;">person</span>
                                        </div>
                                        {{ $user->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-{{ $user->role == 'admin' ? 'purple' : 'gray' }}-100 text-{{ $user->role == 'admin' ? 'purple' : 'gray' }}-800 text-xs font-medium px-2.5 py-0.5 rounded uppercase inline-flex items-center">
                                         @if($user->role == 'admin')
                                            <span class="material-icons text-xs mr-1">verified_user</span>
                                        @else
                                            <span class="material-icons text-xs mr-1">person_outline</span>
                                        @endif
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('users.edit', $user->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Hapus">
                                                <span class="material-icons">delete</span>
                                            </button>
                                        </form>
                                        @endif
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
