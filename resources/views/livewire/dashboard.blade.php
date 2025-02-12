<div class="relative w-full min-h-screen">
    <!-- Search Bar -->
    <div class="w-full bg-gray-100 shadow-md py-4 px-6 flex items-center space-x-2 sticky top-0 z-10">
        <select wire:model="kategori" class="px-4 py-2 bg-gray-800 text-white rounded-lg">
            @foreach($categories as $category)
                <option value="{{ $category }}">{{ $category }}</option>
            @endforeach
        </select>

        <input type="text" wire:model="query" placeholder="Cari sesuatu..." 
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-gray-300 focus:outline-none">

        <div class="relative">
            <button wire:click="search" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Search
            </button>

            <!-- Efek Loading -->
            <div wire:loading wire:target="search" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <svg class="animate-spin h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0h-2a6 6 0 00-12 0H4z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Layout Dashboard -->
    <div class="flex w-full px-4 mt-4">
        <!-- Konten Utama -->
        <div class="flex-1 p-4 relative">
            <div wire:loading class="absolute inset-0 bg-gray-100 bg-opacity-75 flex items-center justify-center z-10">
                <div class="flex items-center space-x-2">
                    <svg class="animate-spin h-8 w-8 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0h-2a6 6 0 00-12 0H4z"></path>
                    </svg>
                    <span class="text-gray-700 font-semibold">Memuat...</span>
                </div>
            </div>

            <!-- Jika pencarian telah dilakukan dan tidak ditemukan hasil -->
            @if($query && empty($filteredTempat))
                <div class="flex flex-col items-center justify-center min-h-[300px] text-gray-600 animate-fadeIn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mb-4 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 00-10 10v5a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2v-5a10 10 0 00-10-10z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75h.01M14.25 9.75h.01M9 15.75s.75-1.5 3-1.5 3 1.5"/>
                    </svg>
                    <h2 class="text-2xl font-semibold">Pencarian tidak ditemukan :(</h2>
                    <p class="text-gray-500 mt-2">Coba gunakan kata kunci lain atau cari di kategori lain.</p>
                </div>
            @else
                <div class="mt-6">
                    @livewire('tempat')
                </div>
            @endif
        </div>

        <!-- Kontainer Berita -->
        <div class="relative w-[350px] h-[600px] bg-gray-800 shadow-lg p-4 rounded-2xl flex-shrink-0 ml-4">
            <div wire:loading wire:target="nextBerita, prevBerita" class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-10">
                <div class="animate-spin h-10 w-10 border-4 border-white border-t-transparent rounded-full"></div>
            </div>

            <button wire:click="prevBerita" 
                class="absolute left-[10px] top-1/2 -translate-y-1/2 bg-white text-gray-800 p-3 rounded-full hover:bg-gray-700 transition z-20 transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="relative w-full h-auto overflow-hidden">
                <div class="relative w-full transition-all duration-500 ease-in-out animate-fadeIn"
                     wire:key="{{ $beritaIndex }}">
                    <img src="{{ $beritaList[$beritaIndex]['img'] }}" alt="Berita" 
                         class="w-full h-auto object-contain rounded-lg transform transition-transform hover:scale-105">
                    <p class="mt-2 text-white text-sm">{{ $beritaList[$beritaIndex]['text'] }}</p>
                </div>
            </div>
            
            <button wire:click="nextBerita" 
                class="absolute right-2 top-1/2 -translate-y-1/2 bg-white text-gray-800 p-3 rounded-full hover:bg-gray-700 transition transform hover:scale-110">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>
</div>
