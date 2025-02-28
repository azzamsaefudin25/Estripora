<div class="h-screen w-full bg-gray-100 flex flex-col relative">
    <!-- Tombol Kembali (Pojok Kiri Atas) -->
    <div class="absolute top-4 left-4 flex gap-2 z-10">
        <!-- Tombol Kembali ke Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="p-2 bg-gray-200 hover:bg-gray-300 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white shadow-md p-4 flex items-center justify-center">
        <h1 class="text-xl font-bold text-gray-900">{{ $tempat->nama ?? 'Tempat Tidak Ditemukan' }}</h1>
    </div>

    <!-- Konten -->
    <div class="flex flex-col lg:flex-row flex-grow">
        <div class="lg:w-1/2 w-full h-60 lg:h-full flex items-center justify-center bg-gray-200">
            <img src="{{ asset($tempat->img ?? '/images/default.jpg') }}" 
                alt="Gambar Tempat" 
                class="w-full h-full object-cover shadow-lg">
        </div>

        <div class="lg:w-1/2 w-full p-8 flex flex-col space-y-6">
            <p class="text-lg text-gray-700 leading-relaxed">{{ $tempat->deskripsi ?? 'Deskripsi tidak tersedia.' }}</p>

            <!-- Tombol Penyewaan -->
            <div class="flex space-x-4">
                <a href="{{ route('penyewaan.perjam', ['id_lokasi' => $tempat->id]) }}"
                    class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-md">
                    Penyewaan Per Jam
                </a>

                <a href="{{ route('penyewaan.perhari', ['id_lokasi' => $tempat->id]) }}"
                    class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition shadow-md">
                     Penyewaan Per Hari
                 </a>
            </div>
        </div>
    </div>
</div>
