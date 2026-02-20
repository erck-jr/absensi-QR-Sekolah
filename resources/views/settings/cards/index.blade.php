<x-app-layout>
    <x-slot name="title">Template ID Card</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Template ID Card') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-material-card title="Template ID Card" icon="style" color="cyan">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($templates as $template)
                        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                            <h5 class="mb-3 text-md font-bold tracking-tight text-gray-900 capitalize text-center border-b pb-2">
                                @if(str_contains($template->key, 'student'))
                                    Kartu Siswa<br>
                                    <span class="text-xs text-gray-500 font-normal">
                                        {{ str_replace('student_', '', $template->key) == 'front' ? 'Depan' : 'Belakang' }}
                                    </span>
                                @else
                                    Kartu Guru<br>
                                    <span class="text-xs text-gray-500 font-normal">
                                        {{ str_replace('teacher_', '', $template->key) == 'front' ? 'Depan' : 'Belakang' }}
                                    </span>
                                @endif
                            </h5>
                            
                            <div class="mb-4 h-40 bg-gray-50 rounded-lg overflow-hidden relative flex items-center justify-center border border-gray-200">
                                @if($template->file_name && $template->file_name !== 'default.png')
                                    <img src="{{ asset('templates_card/' . $template->file_name) }}" alt="{{ $template->key }}" class="max-w-full max-h-full object-contain">
                                @else
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <span class="material-icons text-4xl mb-1">image_not_supported</span>
                                        <span class="text-xs">No Image</span>
                                    </div>
                                @endif
                            </div>

                            <form action="{{ route('card-templates.update', $template->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="mb-4">
                                    <input class="block w-full text-xs text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" id="file_{{ $template->id }}" name="file_name" type="file" required>
                                </div>
                                <button type="submit" class="w-full text-white bg-cyan-600 hover:bg-cyan-700 focus:ring-4 focus:ring-cyan-300 font-medium rounded-lg text-sm px-4 py-2 flex items-center justify-center shadow-sm">
                                    <span class="material-icons text-sm mr-2">upload</span> Upload
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </x-material-card>
        </div>
    </div>
</x-app-layout>

