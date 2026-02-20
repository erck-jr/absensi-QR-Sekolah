<x-app-layout>
    <x-slot name="title">Template Pesan</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Template Pesan WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Template Pesan WhatsApp" icon="message" color="teal">
                <div class="mb-4 p-4 text-sm text-blue-800 rounded-lg bg-blue-50" role="alert">
                    <span class="font-medium">Variables Available:</span> {name}, {nis} (Siswa), {nuptk} (Guru), {time}, {date}, {status}
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($messages as $message)
                    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <h5 class="mb-3 text-lg font-bold tracking-tight text-gray-900 border-b pb-2">
                            {{ ucwords(str_replace('_', ' ', $message->key)) }}
                        </h5>
                        <form action="{{ route('message-templates.update', $message->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <label class="block mb-2 text-xs font-medium text-gray-500 uppercase">Isi Pesan</label>
                                <textarea name="content" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-teal-500 focus:border-teal-500" required>{{ $message->content }}</textarea>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-teal-600 rounded-lg hover:bg-teal-700 focus:ring-4 focus:outline-none focus:ring-teal-300 shadow-sm">
                                <span class="material-icons text-sm mr-2">save</span> Update Template
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

