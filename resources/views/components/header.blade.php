<header class="bg-white shadow-md">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <!-- Logo -->
        <div class="inline-flex items-center">
        <img src="{{ asset('images/estriporalogo.png') }}" alt="Estripora Logo" class="w-10 h-10">
        </div>
        <a href="/dashboard" class="text-2xl font-bold text-blue-500">Estripora Kota Semarang</a>

        <!-- Navigation (Desktop) -->
        <nav class="hidden md:flex space-x-6">
            <a href="#" class="text-gray-700 hover:text-blue-600">Home</a>
            <a href="#" class="text-gray-700 hover:text-blue-600">About</a>
            <a href="#" class="text-gray-700 hover:text-blue-600">Services</a>
            <a href="#" class="text-gray-700 hover:text-blue-600">Contact</a>
        </nav>

        <!-- Login/Register Buttons -->
        <div class="hidden md:flex space-x-4">
            <a href="#" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-600 hover:text-white">Login</a>
            <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Register</a>
        </div>

        <!-- Hamburger Menu (Mobile) -->
        <button id="menu-btn" class="md:hidden text-gray-700 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden flex flex-col space-y-2 px-4 py-3 bg-white shadow-md">
        <a href="#" class="text-gray-700 hover:text-blue-600">Home</a>
        <a href="#" class="text-gray-700 hover:text-blue-600">About</a>
        <a href="#" class="text-gray-700 hover:text-blue-600">Services</a>
        <a href="#" class="text-gray-700 hover:text-blue-600">Contact</a>
        <div class="flex flex-col space-y-2 mt-2">
            <a href="#" class="px-4 py-2 text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-600 hover:text-white text-center">Login</a>
            <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center">Register</a>
        </div>
    </div>
</header>
