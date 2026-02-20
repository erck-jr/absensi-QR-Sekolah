<x-guest-layout>
    <x-slot name="title">Selamat Datang</x-slot>
    <div class="text-center space-y-6">
        <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 shadow-sm mb-6">
            <h2 class="text-2xl font-bold text-navy-900 mb-4">
                Selamat Datang
            </h2>
            <p class="text-gray-600 leading-relaxed italic">
                {{ settings('welcome_text') ?? '"Selamat Datang Di Aplikasi Sistem Informasi Absensi Siswa berbasis QR-Code. Silahkan login untuk memulai aplikasi"' }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @auth
                <a href="{{ route('dashboard') }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-navy-900 text-white rounded-xl font-bold hover:bg-navy-800 transition-all shadow-lg hover:shadow-navy-900/20 group">
                    <span class="material-icons">dashboard</span>
                    Buka Dashboard
                    <span class="material-icons group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-navy-900 text-white rounded-xl font-bold hover:bg-navy-800 transition-all shadow-lg hover:shadow-navy-900/20 group">
                    <span class="material-icons text-white">login</span>
                    Login ke Akun
                    <span class="material-icons group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            @endauth

            <a href="{{ route('public.attendance.index') }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 bg-white border-2 border-orange-500 text-orange-600 rounded-xl font-bold hover:bg-orange-50 transition-all shadow-md group">
                <span class="material-icons">qr_code_scanner</span>
                Cek Kehadiran Siswa
                <span class="material-icons group-hover:scale-110 transition-transform">search</span>
            </a>
        </div>
    </div>
</x-guest-layout>
