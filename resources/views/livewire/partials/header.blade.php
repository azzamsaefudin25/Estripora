<header class="bg-white shadow-md sticky top-0 z-50 w-full">
    <div class="container mx-auto px-4 py-3">
        <!-- Desktop Header -->
        <div class="flex justify-between items-center">
            <!-- Mobile Sidebar Toggle Button -->
            <button wire:click="$dispatch('toggleSidebar')"
                class="md:hidden p-2 text-gray-700 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-all duration-200 mr-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
            {{-- @if ($showMobileMenu)
                <div wire:click="closeMobileMenu" class="fixed inset-0 z-40 md:hidden">
                </div>
            @endif --}}
            <!-- Logo Section -->
            <div class="inline-flex items-center">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/estriporalogo.png') }}" alt="Estripora Logo" class="w-10 h-10">
                </a>
            </div>
            <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-gray-800 hidden lg:block">ESTRIPORA KOTA
                SEMARANG</a>
            <a href="{{ route('dashboard') }}" class="text-1xl font-bold text-gray-800 hidden sm:block lg:hidden">ESTRIPORA KOTA
                SEMARANG</a>
            <!-- Mobile Title (Only visible on very small screens) -->
            <div class="flex-1 text-center sm:hidden">
                <a href="{{ route('dashboard') }}"
                    class="text-sm font-bold text-gray-800 hover:text-red-600 transition-colors duration-300">
                    ESTRIPORA
                </a>
            </div>

            <!-- Desktop Auth Section -->
            <div class="hidden md:flex items-center">
                @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center space-x-2 px-3 py-2 text-gray-700 hover:text-red-600 hover:bg-gray-50 rounded-lg transition-all duration-300 group">
                            <div class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 transition-transform duration-200 group-hover:rotate-180"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Desktop Dropdown Menu -->
                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95" @click.away="open = false"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                            <a href="{{ route('indexProfile') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profile Saya
                            </a>
                            <a href="{{ route('riwayat') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Riwayat Pesanan
                            </a>
                            <a href="{{ route('ubahpassword') }}"
                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-red-600 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                Ubah Password
                            </a>
                            <hr class="my-2 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('login') }}"
                            class="flex items-center px-4 py-2 text-gray-800 border border-gray-300 rounded-lg hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span class="font-medium">Login</span>
                        </a>
                        <a href="{{ route('register') }}"
                            class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300 shadow-sm hover:shadow-md">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            <span class="font-medium">Register</span>
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Mobile Header Menu Button -->
            <button wire:click="toggleMobileMenu"
                class="md:hidden p-2 text-gray-700 hover:text-red-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4"
                        d='M12 5v.01M12 12v.01M12 19v.01'></path>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div class="md:hidden {{ $showMobileMenu ? 'block' : 'hidden' }} mt-4 border-t border-gray-200 pt-4">
            @guest
                <div class="flex flex-col space-y-3">
                    <a href="{{ route('login') }}"
                        class="flex items-center justify-center px-4 py-3 text-gray-800 border border-gray-300 rounded-lg hover:bg-red-600 hover:text-white hover:border-red-600 transition-all duration-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">Login</span>
                    </a>
                    <a href="{{ route('register') }}"
                        class="flex items-center justify-center px-4 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all duration-300">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                        </svg>
                        <span class="font-medium">Register</span>
                    </a>
                </div>
            @else
                <div class="space-y-1">
                    <div class="px-4 py-3 bg-gray-50 rounded-lg mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center">
                                <span class="text-white font-semibold">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 text-sm sm:text-base">{{ auth()->user()->name }}</p>
                                <p class="text-xs sm:text-sm text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('indexProfile') }}"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Profile Saya
                    </a>
                    <a href="{{ route('riwayat') }}"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Riwayat Pesanan
                    </a>
                    <a href="{{ route('ubahpassword') }}"
                        class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-red-600 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Ubah Password
                    </a>
                    <hr class="my-3 border-gray-200">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
</header>
