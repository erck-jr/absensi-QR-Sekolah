<x-app-layout>
    <x-slot name="title">Notifikasi WA</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Notifikasi & Gateway WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Section 1: Gateway Management -->
            <x-material-card title="Gateway WhatsApp" icon="chat" color="green">
                <x-slot name="actions">
                    <a href="{{ route('wagateways.create') }}" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center shadow">
                        <span class="material-icons text-sm mr-1">add</span> Tambah Gateway
                    </a>
                </x-slot>

                <div class="relative overflow-x-auto">
                    <table id="search-table" class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Nama</th>
                                <th scope="col" class="px-6 py-3">URL</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($wagateways as $gateway)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $gateway->name }}</td>
                                <td class="px-6 py-4">{{ $gateway->api_url }}</td>
                                <td class="px-6 py-4">
                                    @if($gateway->is_active)
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aktif</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="{{ route('wagateways.edit', $gateway->id) }}" class="text-orange-500 hover:text-orange-700" title="Edit">
                                            <span class="material-icons">edit</span>
                                        </a>
                                        <form action="{{ route('wagateways.destroy', $gateway->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus gateway ini?')">
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

            <!-- Section 2: Notification Activation -->
            <x-material-card title="Aktifasi Notifikasi Otomatis" icon="notifications" color="blue">
                <form action="{{ route('wagateways.settings.update') }}" method="POST">
                    @csrf
                    <div class="grid gap-6 mb-6 md:grid-cols-2">
                        <!-- Notifikasi Siswa -->
                        <div class="p-6 bg-blue-50 rounded-xl border border-blue-100 shadow-sm">
                            <h4 class="font-bold text-lg text-blue-800 mb-4 flex items-center">
                                <span class="material-icons mr-2">person</span> Notifikasi Siswa
                            </h4>
                            <div class="flex flex-col gap-4">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" name="wa_notif_student_in" value="1" {{ ($settings['wa_notif_student_in'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 transition-colors"></div>
                                    <span class="ms-3 text-sm font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">Kirim WA Saat Siswa Masuk</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" name="wa_notif_student_out" value="1" {{ ($settings['wa_notif_student_out'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-blue-600 transition-colors"></div>
                                    <span class="ms-3 text-sm font-semibold text-gray-700 group-hover:text-blue-600 transition-colors">Kirim WA Saat Siswa Pulang</span>
                                </label>
                            </div>
                        </div>

                        <!-- Notifikasi Guru -->
                        <div class="p-6 bg-green-50 rounded-xl border border-green-100 shadow-sm">
                            <h4 class="font-bold text-lg text-green-800 mb-4 flex items-center">
                                <span class="material-icons mr-2">school</span> Notifikasi Guru
                            </h4>
                            <div class="flex flex-col gap-4">
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" name="wa_notif_teacher_in" value="1" {{ ($settings['wa_notif_teacher_in'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600 transition-colors"></div>
                                    <span class="ms-3 text-sm font-semibold text-gray-700 group-hover:text-green-600 transition-colors">Kirim WA Saat Guru Masuk</span>
                                </label>
                                <label class="inline-flex items-center cursor-pointer group">
                                    <input type="checkbox" name="wa_notif_teacher_out" value="1" {{ ($settings['wa_notif_teacher_out'] ?? '0') == '1' ? 'checked' : '' }} class="sr-only peer">
                                    <div class="relative w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-green-600 transition-colors"></div>
                                    <span class="ms-3 text-sm font-semibold text-gray-700 group-hover:text-green-600 transition-colors">Kirim WA Saat Guru Pulang</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t flex justify-end">
                        <x-primary-button class="flex items-center">
                            <span class="material-icons text-sm mr-2">save</span>
                            {{ __('Simpan Pengaturan Aktifasi') }}
                        </x-primary-button>
                    </div>
                </form>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

