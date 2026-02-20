<nav x-data="{ open: false }" class="bg-navy-900 border-b border-navy-800">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-white" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('welcome')" :active="request()->routeIs('welcome')">
                        <span class="material-icons">home</span>
                    </x-nav-link>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('public.attendance.index')" :active="request()->routeIs('public.attendance.*')">
                        {{ __('Cek Kehadiran') }}
                    </x-nav-link>
                    <x-nav-link :href="route('scanner.index')" :active="request()->routeIs('scanner.*')">
                        {{ __('Scanner') }}
                    </x-nav-link>
                    <x-nav-link :href="route('guests.index')" :active="request()->routeIs('guests.*')">
                        {{ __('Buku Tamu') }}
                    </x-nav-link>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 bg-navy-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                                    <div>Laporan</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('reports.students')">{{ __('Kehadiran Siswa') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('reports.teachers')">{{ __('Kehadiran Guru') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @if(Auth::user()->role === 'admin')
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 bg-navy-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                                    <div>Master Data</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('students.index')">{{ __('Data Siswa') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('teachers.index')">{{ __('Data Guru') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('classes.index')">{{ __('Data Kelas') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('levels.index')">{{ __('Data Tingkat') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('shifts.index')">{{ __('Data Shift') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('holidays.index')">{{ __('Data Libur') }}</x-dropdown-link>
                                @if(Auth::user()->role === 'admin')
                                <x-dropdown-link :href="route('generator.index')">{{ __('Generate ID Card') }}</x-dropdown-link>
                                @endif
                            </x-slot>
                        </x-dropdown>
                    </div>
                    <div class="hidden sm:flex sm:items-center sm:ms-6">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 bg-navy-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                                    <div>Pengaturan</div>
                                    <div class="ms-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('settings.index')">{{ __('Aplikasi') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('wagateways.index')">{{ __('Data WA Gateway') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('users.index')">{{ __('Manajemen User') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('message-templates.index')">{{ __('Template Pesan') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('card-templates.index')">{{ __('Template ID Card') }}</x-dropdown-link>
                                <x-dropdown-link :href="route('walogs.index')">{{ __('Log WhatsApp') }}</x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 bg-navy-900 hover:text-white focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-300 hover:text-white hover:bg-navy-800 focus:outline-none focus:bg-navy-800 focus:text-white transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('welcome')" :active="request()->routeIs('welcome')">
                {{ __('Home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('public.attendance.index')" :active="request()->routeIs('public.attendance.*')">
                {{ __('Cek Kehadiran') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('scanner.index')" :active="request()->routeIs('scanner.*')">
                {{ __('Scanner') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('guests.index')" :active="request()->routeIs('guests.*')">
                {{ __('Buku Tamu') }}
            </x-responsive-nav-link>

            <!-- Mobile Reports Dropdown -->
            <div x-data="{ open: false }" class="border-t border-navy-800">
                <button @click="open = ! open" class="flex items-center w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-navy-800 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                    <div class="flex-1">{{ __('Laporan') }}</div>
                    <svg class="fill-current h-4 w-4" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="open" class="ps-4 space-y-1">
                    <x-responsive-nav-link :href="route('reports.students')" :active="request()->routeIs('reports.students')">
                        {{ __('Kehadiran Siswa') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('reports.teachers')" :active="request()->routeIs('reports.teachers')">
                        {{ __('Kehadiran Guru') }}
                    </x-responsive-nav-link>
                </div>
            </div>

            @if(Auth::user()->role === 'admin')
            <!-- Mobile Master Data Dropdown -->
            <div x-data="{ open: false }" class="border-t border-navy-800">
                <button @click="open = ! open" class="flex items-center w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-navy-800 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                    <div class="flex-1">{{ __('Master Data') }}</div>
                    <svg class="fill-current h-4 w-4" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="open" class="ps-4 space-y-1">
                     <x-responsive-nav-link :href="route('students.index')" :active="request()->routeIs('students.*')">{{ __('Data Siswa') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('teachers.index')" :active="request()->routeIs('teachers.*')">{{ __('Data Guru') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('classes.index')" :active="request()->routeIs('classes.*')">{{ __('Data Kelas') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('levels.index')" :active="request()->routeIs('levels.*')">{{ __('Data Tingkat') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('shifts.index')" :active="request()->routeIs('shifts.*')">{{ __('Data Shift') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('holidays.index')" :active="request()->routeIs('holidays.*')">{{ __('Data Libur') }}</x-responsive-nav-link>
                     <x-responsive-nav-link :href="route('generator.index')" :active="request()->routeIs('generator.*')">{{ __('Generate ID Card') }}</x-responsive-nav-link>
                </div>
            </div>

            <!-- Mobile Settings Dropdown -->
            <div x-data="{ open: false }" class="border-t border-navy-800">
                <button @click="open = ! open" class="flex items-center w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-300 hover:text-white hover:bg-navy-800 hover:border-gray-300 focus:outline-none transition duration-150 ease-in-out">
                    <div class="flex-1">{{ __('Pengaturan') }}</div>
                    <svg class="fill-current h-4 w-4" :class="{'rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="open" class="ps-4 space-y-1">
                    <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')">{{ __('Aplikasi') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('wagateways.index')" :active="request()->routeIs('wagateways.*')">{{ __('Data WA Gateway') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">{{ __('Manajemen User') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('message-templates.index')" :active="request()->routeIs('message-templates.*')">{{ __('Template Pesan') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('card-templates.index')" :active="request()->routeIs('card-templates.*')">{{ __('Template ID Card') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('walogs.index')" :active="request()->routeIs('walogs.*')">{{ __('Log WhatsApp') }}</x-responsive-nav-link>
                </div>
            </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-navy-800">
            <div class="px-4">
                <div class="font-medium text-base text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
