<div class="relative w-full min-h-screen">
    <!-- Search Bar -->
    <div
        class="flex flex-col md:flex-row md:items-start w-full items-center bg-gray-100 shadow-md py-4 px-6 sticky top-0 z-10 gap-4 md:gap-2">
        <select wire:model.live="kategori"
            class="w-full lg:w-auto max-w-[500px] px-4 py-2 bg-gray-800 text-white rounded-lg">
            <option value="">-- Pilih Kategori --</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>

        <input type="text" wire:model.live.debounce.300ms="query" placeholder="Cari sesuatu..."
            class="w-full max-w-[500px] px-4 py-2 border border-gray-300 rounded-lg focus:ring focus:ring-gray-300 focus:outline-none">

        <div class="relative">
            <button wire:click="search" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-700 transition">
                Search
            </button>

            <!-- Efek Loading -->
            <div wire:loading wire:target="search, query, kategori"
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                <svg class="animate-spin h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0h-2a6 6 0 00-12 0H4z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Layout Dashboard -->
    <div class="flex flex-col-reverse md:flex-row md:items-start ">
        <!-- Konten Utama -->
        <div class="flex-1 p-4 relative">
            <div wire:loading wire:target="search, query, kategori"
                class="absolute inset-0 bg-gray-100 bg-opacity-75 flex items-center justify-center z-10">
                <div class="flex items-center space-x-2">
                    <svg class="animate-spin h-8 w-8 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0116 0h-2a6 6 0 00-12 0H4z"></path>
                    </svg>
                    <span class="text-gray-700 font-semibold">Memuat...</span>
                </div>
            </div>

            <div class="mt-6">
                @livewire('tempats')
            </div>
        </div>

        <!-- Kontainer Berita -->
        <div class="relative w-full md:w-[350px] bg-gray-800 shadow-lg p-4 rounded-2xl flex-shrink-0 md:ml-4 mb-4 md:mb-0 flex flex-col">

            {{-- Gambar berita --}}
            <div class="flex-1 flex items-center justify-center overflow-hidden mb-2">
                @if (count($beritaList) > 0)
                    <img
                        src="{{ $beritaList[$beritaIndex]['img'] }}"
                        alt="Berita {{ $beritaIndex + 1 }}"
                        class="max-h-[450px] object-contain rounded-lg transform transition-transform hover:scale-105"
                    />
                @else
                    <p class="text-gray-400">Belum ada berita.</p>
                @endif
            </div>

            {{-- Baris tombol prev & next --}}
            <div class="flex justify-between mb-2">
                <button
                wire:click="prevBerita"
                class="bg-white text-gray-800 p-3 rounded-full hover:bg-gray-200 transition transform hover:scale-110"
                aria-label="Berita Sebelumnya"
                >
                ‹
                </button>
                <button
                wire:click="nextBerita"
                class="bg-white text-gray-800 p-3 rounded-full hover:bg-gray-200 transition transform hover:scale-110"
                aria-label="Berita Berikutnya"
                >
                ›
                </button>
            </div>

            {{-- Isi teks berita --}}
            @if (count($beritaList) > 0)
                <p class="text-white text-center text-sm whitespace-pre-line">
                    {{ strip_tags($beritaList[$beritaIndex]['text']) }}
                </p>
            @endif

        </div>

    </div>
</div>
