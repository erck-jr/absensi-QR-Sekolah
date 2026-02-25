<x-app-layout>
    <x-slot name="title">Detail Siswa</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white  overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                
                <!-- Profile Section -->
                @if($student->photo)
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('storage/photo/students/' . $student->photo) }}" alt="Foto Siswa" class="w-32 h-32 object-cover rounded-full border-2 border-gray-300">
                    </div>
                @endif
                <h3 class="text-2xl font-bold mb-2 ">{{ $student->name }}</h3>
                <p class="text-gray-600  mb-6">{{ $student->nis }} - {{ $student->classRoom->name ?? '-' }}</p>

                <!-- Toggle Buttons -->
                <div class="flex justify-center mb-6 border-b border-gray-200 ">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" data-tabs-toggle="#myTabContent" role="tablist">
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300  group" id="qrcode-tab" data-tabs-target="#qrcode" type="button" role="tab" aria-controls="qrcode" aria-selected="false" onclick="switchTab('qrcode')">QR Code</button>
                        </li>
                        <li class="mr-2" role="presentation">
                            <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300  group" id="idcard-tab" data-tabs-target="#idcard" type="button" role="tab" aria-controls="idcard" aria-selected="false" onclick="switchTab('idcard')">ID Card</button>
                        </li>
                    </ul>
                </div>

                <!-- QR Code Content -->
                <div id="qrcode-content" class="tab-content">
                    <div class="flex justify-center mb-6 bg-white p-4 inline-block rounded-lg">
                        {!! $qrcode !!}
                    </div>
                </div>

                <!-- ID Card Content -->
                <div id="idcard-content" class="tab-content hidden">
                    @if($hasIdCard)
                        <div class="flex flex-col items-center mb-6">
                            <img src="{{ $idCardUrl }}?t={{ time() }}" alt="ID Card" class="max-w-md w-full shadow-lg rounded-lg mb-4">
                            <div class="flex flex-row space-x-2">
                                <a href="{{ $idCardUrl }}" download class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none">
                                    Unduh ID Card
                                </a>
                                <form action="{{ route('students.generate-card', $student->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-white bg-yellow-500 hover:bg-yellow-600 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none">
                                        Regenerate
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <p class="text-gray-500  mb-4">ID Card belum digenerate.</p>
                            <form action="{{ route('students.generate-card', $student->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-white bg-purple-700 hover:bg-purple-800 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 mb-2 focus:outline-none">
                                    Generate ID Card
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="mt-6 border-t pt-6 ">
                    <a href="{{ route('students.index') }}" class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2      ">Kembali</a>
                    <button onclick="window.print()" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 mr-2 mb-2   focus:outline-none ">Cetak Halaman</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all
            document.getElementById('qrcode-content').classList.add('hidden');
            document.getElementById('idcard-content').classList.add('hidden');
            
            // Reset buttons
            document.getElementById('qrcode-tab').classList.remove('text-blue-600', 'border-blue-600', 'active');
            document.getElementById('qrcode-tab').classList.add('border-transparent');
            
            document.getElementById('idcard-tab').classList.remove('text-blue-600', 'border-blue-600', 'active');
            document.getElementById('idcard-tab').classList.add('border-transparent');

            // Show selected
            document.getElementById(tabName + '-content').classList.remove('hidden');
            
            // Highlight button
            const activeBtn = document.getElementById(tabName + '-tab');
            activeBtn.classList.remove('border-transparent');
            activeBtn.classList.add('text-blue-600', 'border-blue-600', 'active');
        }

        // Initialize default tab
        switchTab('qrcode');
    </script>
</x-app-layout>

