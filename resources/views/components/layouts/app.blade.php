<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tambahkan di <head> atau sebelum </body> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css' rel='stylesheet' />

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/id.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>{{ $title ?? 'Estripora' }}</title>
    <!-- Tambahkan di bagian head atau sebelum </body> -->
    @vite('resources/css/app.css', 'resources/js/app.js')
    @livewireStyles
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <!-- Wrapper untuk sidebar dan konten utama -->
    <div class="flex flex-1">
        <!-- Sidebar -->
        @livewire('partials.sidebar')

        <!-- Layout Utama -->
        <div class="md:ml-64 flex flex-col flex-1 min-h-screen ">

            <!-- Header -->
            @livewire('partials.header')

            <!-- Konten Utama -->
            <main class="flex-1 container mx-auto p-5">

                @if (session()->has('success'))
                    <div x-data="{ show: true }" x-show="show"
                        class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r shadow-md relative">
                        <div class="flex items-center justify-between">
                            <p class="text-green-800 text-sm font-medium">
                                {{ session('success') }}
                            </p>
                            <button @click="show = false"
                                class="text-green-400 hover:opacity-75 transition-opacity duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div x-data="{ show: true }" x-show="show"
                        class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r shadow-md relative">
                        <div class="flex items-center justify-between">
                            <p class="text-red-800 text-sm font-medium">
                                {{ session('error') }}
                            </p>
                            <button @click="show = false"
                                class="text-red-400 hover:opacity-75 transition-opacity duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
                {{ $slot }}

            </main>

            <!-- Footer -->
            <footer class="bg-white text-black mt-auto">
                <div class="container mx-auto px-5 py-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-12 items-start">
                        <!-- Kontak Kami Section -->
                        <div class="flex flex-col justify-start">
                            <h3 class="text-sm font-bold uppercase tracking-wide mb-6 text-black">KONTAK KAMI</h3>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1 mr-4">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold mb-1 text-black">Telepon</h4>
                                        <p class="text-sm text-gray-600">(024) 3513366</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1 mr-4">
                                        <svg class="w-5 h-5 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold mb-1 text-black">Email</h4>
                                        <p class="text-sm text-red-600">info@semarang.go.id</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mt-1 mr-4">
                                        <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-semibold mb-1 text-black">Alamat</h4>
                                        <p class="text-sm text-gray-600 leading-relaxed">Jl. Pemuda No.148, Sekayu,
                                            Semarang
                                            Tengah</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Link Terkait Section -->
                        <div class="flex flex-col justify-start text-left md:items-center md:text-left">

                            <h3 class="text-sm font-bold uppercase tracking-wide mb-6 text-black">LINK TERKAIT</h3>
                            <ul class="space-y-3">
                                <li><a href="#"
                                        class="text-sm text-gray-600 hover:text-red-600 transition-colors duration-300">BKD
                                        Kota Semarang</a></li>
                            </ul>
                        </div>

                        <!-- Sitemap Section -->
                        <div class="flex flex-col justify-start items-start text-left md:items-end md:text-right">
                            
                            <h3 class="text-sm font-bold uppercase tracking-wide mb-6 text-black">SITEMAP</h3>
                            <ul class="space-y-3">
                                <li><a href="#"
                                        class="text-sm text-gray-600 hover:text-red-600 transition-colors duration-300">ESTRIPORA
                                        Kota Semarang</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Footer Bottom -->
                    <div class="border-t border-gray-300 mt-10 pt-8 text-center">
                        <p class="text-sm font-semibold text-black">2025 ESPTRD Kota Semarang</p>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML =
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />`;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML =
                    `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />`;
            }
        });
    </script>
    @livewireScripts
</body>

</html>
