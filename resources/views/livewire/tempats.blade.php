<div class="mt-6">
    @if($noResults)
        <div class="flex flex-col items-center justify-center min-h-[300px] text-gray-600 animate-fadeIn">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mb-4 text-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2a10 10 0 00-10 10v5a2 2 0 002 2h3l3 3 3-3h3a2 2 0 002-2v-5a10 10 0 00-10-10z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75h.01M14.25 9.75h.01M9 15.75s.75-1.5 3-1.5 3 1.5"/>
            </svg>
            <h2 class="text-2xl font-semibold">Pencarian tidak ditemukan :(</h2>
            <p class="text-gray-500 mt-2">Coba gunakan kata kunci lain atau cari di kategori lain.</p>
        </div>
    @else
        @foreach ($groupedTempats as $kategori => $tempats)
            <!-- Nama Kategori -->
            <h2 class="text-lg font-bold text-gray-800 mt-6">{{ $kategori }}</h2>

            <!-- List Tempat dalam Kategori -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
                @foreach ($tempats as $tempat)
                    <div
                        class="bg-white shadow-md rounded-b-xl overflow-hidden transform transition-transform duration-300 hover:scale-105">
                        <!-- Gambar Tempat -->
                        <div class="relative w-full h-48">
                            <img src="{{ asset('storage/'.$tempat['image']) }}" class="w-full h-full object-cover"
                                alt="{{ $tempat['nama'] }}">

                            <!-- Overlay Gradient -->
                            <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black to-transparent">
                            </div>
                            <!-- Nama Tempat -->
                            <h2 class="absolute bottom-3 left-4 text-white font-bold text-lg">{{ $tempat['nama'] }}</h2>
                        </div>

                        <!-- Kontainer Harga & Tombol -->
                        <div class="flex justify-between items-center px-4 py-3 bg-indigo-100">
                            <p class="text-gray-700 font-semibold">{{ $tempat['rentang_harga'] }} /
                                {{ $tempat['kategori_sewa'] }}</p>
                            <a href="{{ route('detail-tempat', $tempat['id']) }}"
                                class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-600 transition">
                                DETAIL
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    @endif
</div>