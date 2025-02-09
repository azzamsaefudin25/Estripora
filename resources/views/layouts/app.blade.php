<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Estripora' }}</title>
    
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    @include('components.sidebar')

    <!-- Layout Utama -->
    <div class="md:ml-64 flex flex-col min-h-screen">
        <!-- Header -->
        @include('components.header')

        <!-- Konten Utama -->
        <main class="flex-1 container mx-auto p-5">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white text-center py-4 shadow mt-auto">
            <p class="text-gray-600">&copy; 2025 Estripora. All rights reserved.</p>
        </footer>
    </div>

</body>
</html>
