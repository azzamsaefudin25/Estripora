<div class="mt-6">
    @foreach($filteredTempat as $kategori => $tempatList)
        <!-- Nama Kategori -->
        <h2 class="text-lg font-bold text-gray-800 mt-6">{{ $kategori }}</h2>

        <!-- List Tempat dalam Kategori -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-2">
            @foreach($tempatList as $tempat)
                <div class="bg-white shadow-md rounded-b-xl overflow-hidden transform transition-transform duration-300 hover:scale-105">
                    <!-- Gambar Tempat -->
                    <div class="relative w-full h-48">
                        <img src="{{ $tempat['img'] }}" class="w-full h-full object-cover">
                        <!-- Overlay Gradient -->
                        <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-black to-transparent"></div>
                        <!-- Nama Tempat -->
                        <h2 class="absolute bottom-3 left-4 text-white font-bold text-lg">{{ $tempat['nama'] }}</h2>
                    </div>

                    <!-- Kontainer Harga & Tombol -->
                    <div class="flex justify-between items-center px-4 py-3 bg-indigo-100">
                        <p class="text-gray-700 font-semibold">{{ $tempat['harga'] }}</p>
                        <a href="{{ route('detail-tempat', ['id' => $tempat['id']]) }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-600 transition">
                            DETAIL
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
