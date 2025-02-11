<div class="relative w-full min-h-screen">
    <!-- Search Bar (Tepat di Bawah Header, BUKAN Bagian dari Header) -->
    <div class="w-full bg-gray-100 shadow-md py-4 px-6 flex items-center space-x-2 sticky top-0 z-10">
        <!-- Dropdown Kategori -->
        <select wire:model="kategori" class="px-4 py-2 bg-gray-800 text-white rounded-lg">
            @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>

        <!-- Input Search -->
        <input type="text" wire:model="query" placeholder="Cari sesuatu..." 
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-gray-300 focus:outline-none">

        <!-- Tombol Cari -->
        <button wire:click="search" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Search
        </button>
    </div>

    <!-- Layout Dashboard -->
    <div class="flex w-full px-4 mt-4">
        <!-- Konten Utama  -->
        <div class="flex-1 p-4">
            <h1 class="text-xl font-bold">Dashboard Page</h1>
            <p>Hanya cinta dan malam</p>
        </div>

        <!-- Kontainer Berita  -->
        <div class="relative w-[350px] h-[600px] bg-gray-800 shadow-lg p-4 rounded-2xl flex-shrink-0 ml-4">

            <!-- Tombol Previous  -->
            <button wire:click="prevBerita" class="absolute left-[10px] top-1/2 -translate-y-1/2 bg-white text-gray-800 p-3 rounded-full hover:bg-gray-700 transition z-20">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Berita -->
            <div class="relative w-full h-auto overflow-hidden">
                <div class="relative w-full transition-all duration-500 ease-in-out animate-fadeIn"
                     wire:key="{{ $beritaIndex }}">
                    <img src="{{ $beritaList[$beritaIndex]['img'] }}" alt="Berita" 
                         class="w-full h-auto object-contain rounded-lg">
                    <p class="mt-2 text-white text-sm">{{ $beritaList[$beritaIndex]['text'] }}</p>
                </div>
            </div>
            
       
            <!-- Tombol Next -->
            <button wire:click="nextBerita" class="absolute right-2 top-1/2 -translate-y-1/2 bg-white text-gray-800 p-3 rounded-full hover:bg-gray-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</div>
