<x-app-layout>
    <x-slot name="title">Log WhatsApp</x-slot>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Log WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Riwayat Pengiriman Pesan WA" icon="history" color="navy">
                <!-- Filters -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <form action="{{ route('walogs.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                        <div class="w-full md:w-auto">
                            <label for="date" class="block mb-1 text-xs font-medium text-gray-700 uppercase">Tanggal</label>
                            <input type="date" name="date" id="date" value="{{ request('date') }}" 
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="w-full md:w-auto">
                            <label for="status" class="block mb-1 text-xs font-medium text-gray-700 uppercase">Status</label>
                            <select name="status" id="status" 
                                class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Semua Status</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Terkirim (Sent)</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Gagal (Failed)</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Kedaluwarsa (Expired)</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Tertunda (Pending)</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center flex items-center">
                                <span class="material-icons text-sm mr-1">filter_alt</span> Filter
                            </button>
                            @if(request()->anyFilled(['date', 'status']))
                                <a href="{{ route('walogs.index') }}" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center">
                                    <span class="material-icons text-sm mr-1">restart_alt</span> Reset
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Waktu</th>
                                <th scope="col" class="px-6 py-3">Penerima</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Pesan</th>
                                <th scope="col" class="px-6 py-3">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $log->recipient_number }}
                                        <div class="text-xs text-gray-400 capitalize">{{ $log->attendance_type }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log->status === 'sent')
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded border border-green-400">Terkirim</span>
                                        @elseif($log->status === 'failed')
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded border border-red-400">Gagal</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded border border-yellow-400">{{ ucfirst($log->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate" title="{{ $log->message_content }}">
                                        {{ $log->message_content }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log->error_details)
                                            <span class="text-xs text-red-600 italic" title="{{ $log->error_details }}">
                                                {{ Str::limit($log->error_details, 50) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-400 italic">
                                        Belum ada riwayat pengiriman pesan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>
