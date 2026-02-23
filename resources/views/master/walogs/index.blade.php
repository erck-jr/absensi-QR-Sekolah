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
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-xs">
                                        {{ $log->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900">{{ $log->recipient_number }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ $log->attendance_type }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log->status === 'sent')
                                            <span class="bg-green-100 text-green-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-green-200">Sent</span>
                                        @elseif($log->status === 'failed')
                                            <span class="bg-red-100 text-red-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-red-200">Failed</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-[10px] uppercase font-bold px-2 py-0.5 rounded border border-yellow-200">{{ $log->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 max-w-xs truncate text-xs" title="{{ $log->message_content }}">
                                        {{ $log->message_content }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button" 
                                            onclick="showLogDetail({{ json_encode([
                                                'recipient' => $log->recipient_number,
                                                'type' => $log->attendance_type,
                                                'status' => $log->status,
                                                'time' => $log->created_at->format('d/m/Y H:i:s'),
                                                'message' => $log->message_content,
                                                'error' => $log->error_details,
                                                'gateway' => $log->gateway->name ?? '-'
                                            ]) }})"
                                            class="text-blue-600 hover:text-blue-900 font-medium text-xs flex items-center">
                                            <span class="material-icons text-xs mr-1">visibility</span> Detail
                                        </button>
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

    <!-- Detail Modal -->
    <div id="logDetailModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full bg-transparent backdrop-blur-xl flex items-center justify-center">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <!-- Header -->
                <div class="flex items-start justify-between p-4 border-b bg-gray-50">
                    <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                        <span class="material-icons mr-2 text-blue-600">info</span> Detail Log WhatsApp
                    </h3>
                    <button type="button" onclick="closeLogModal()" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                        <span class="material-icons">close</span>
                    </button>
                </div>
                <!-- Body -->
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Penerima</span>
                            <span id="detail_recipient" class="text-sm font-semibold text-gray-800">-</span>
                            <span id="detail_type" class="block text-[10px] text-gray-500 capitalize"></span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Status</span>
                            <div id="status_badge_container"></div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Waktu Kirim</span>
                            <span id="detail_time" class="text-sm font-semibold text-gray-800">-</span>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Gateway</span>
                            <span id="detail_gateway" class="text-sm font-semibold text-gray-800">-</span>
                        </div>
                    </div>

                    <div>
                        <span class="block text-[10px] text-gray-400 uppercase font-bold tracking-wider mb-1">Isi Pesan</span>
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 text-sm text-gray-700 whitespace-pre-wrap font-mono leading-relaxed" id="detail_message">
                        </div>
                    </div>

                    <div id="status_details_container" class="rounded-xl border p-4">
                        <span id="detail_status_label" class="block text-[10px] uppercase font-bold tracking-wider mb-1">Detail Status</span>
                        <div class="text-sm italic" id="detail_error">
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <div class="flex items-center p-6 border-t bg-gray-50 justify-end">
                    <button type="button" onclick="closeLogModal()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showLogDetail(data) {
            document.getElementById('detail_recipient').textContent = data.recipient;
            document.getElementById('detail_type').textContent = data.type;
            document.getElementById('detail_time').textContent = data.time;
            document.getElementById('detail_gateway').textContent = data.gateway;
            document.getElementById('detail_message').textContent = data.message;
            
            const badgeContainer = document.getElementById('status_badge_container');
            if (data.status === 'sent') {
                badgeContainer.innerHTML = '<span class="bg-green-100 text-green-800 text-[10px] uppercase font-bold px-2.5 py-1 rounded-full border border-green-200">Terkirim</span>';
            } else if (data.status === 'failed') {
                badgeContainer.innerHTML = '<span class="bg-red-100 text-red-800 text-[10px] uppercase font-bold px-2.5 py-1 rounded-full border border-red-200">Gagal</span>';
            } else {
                badgeContainer.innerHTML = `<span class="bg-yellow-100 text-yellow-800 text-[10px] uppercase font-bold px-2.5 py-1 rounded-full border border-yellow-200">${data.status}</span>`;
            }

            const statusContainer = document.getElementById('status_details_container');
            const statusLabel = document.getElementById('detail_status_label');
            const statusText = document.getElementById('detail_error');
            
            if (data.status === 'sent') {
                statusContainer.className = 'bg-green-50 p-4 rounded-xl border border-green-100';
                statusLabel.className = 'block text-[10px] text-green-600 uppercase font-bold tracking-wider mb-1';
                statusText.className = 'text-sm text-green-700 font-medium';
                statusText.textContent = 'Pesan telah berhasil disampaikan ke gateway dan sedang diproses/terkirim.';
            } else if (data.status === 'failed') {
                statusContainer.className = 'bg-red-50 p-4 rounded-xl border border-red-100';
                statusLabel.className = 'block text-[10px] text-red-600 uppercase font-bold tracking-wider mb-1';
                statusText.className = 'text-sm text-red-700 italic';
                statusText.textContent = data.error || 'Terjadi kesalahan saat pengiriman pesan.';
            } else {
                statusContainer.className = 'bg-yellow-50 p-4 rounded-xl border border-yellow-100';
                statusLabel.className = 'block text-[10px] text-yellow-600 uppercase font-bold tracking-wider mb-1';
                statusText.className = 'text-sm text-yellow-700';
                statusText.textContent = `Status saat ini: ${data.status}`;
            }

            document.getElementById('logDetailModal').classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeLogModal() {
            document.getElementById('logDetailModal').classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</x-app-layout>
