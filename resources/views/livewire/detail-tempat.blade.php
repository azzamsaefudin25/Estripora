<div class="h-screen w-full bg-gray-100 flex flex-col relative">
    <!-- Tombol Kembali (Pojok Kiri Atas) -->
    <div class="absolute top-4 left-4 flex gap-2 z-10">
        <!-- Tombol Kembali ke Dashboard -->
        <a href="{{ route('dashboard') }}" class="text-white p-2 bg-gray-700 hover:bg-gray-900 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
        </a>
    </div>

    <!-- Header -->
    <div class="bg-white shadow-md p-4 flex items-center justify-center">
        <h1 class="text-xl font-bold text-gray-900">{{ $tempat->nama ?? 'Tempat Tidak Ditemukan' }}</h1>
    </div>

    <!-- Konten -->
    <div class="flex flex-col lg:flex-row flex-grow overflow-auto">
        <div class="lg:w-1/2 w-full h-60 lg:h-auto flex items-center justify-center bg-gray-200">
            <img src="{{ asset('storage/' . $tempat->image ?? '/images/default.jpg') }}" alt="Gambar Tempat"
                class="w-auto h-full object-cover shadow-lg">
        </div>
        <div class="lg:w-1/2 w-full p-8 flex flex-col space-y-6 overflow-y-auto">
            <p class="text-lg text-gray-700 leading-relaxed">{{ $tempat->deskripsi ?? 'Deskripsi tidak tersedia.' }}</p>
            <div>
                <label for="id_lokasi" class="block text-sm font-medium text-gray-700 mb-1">Pilih Lokasi</label>
                <select id="id_lokasi" wire:model.live="selectedLokasi"
                    class="w-1/2 px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 ">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach ($lokasi as $lok)
                        <option value="{{ $lok->id_lokasi }}">{{ $lok->nama_lokasi }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Penyewaan -->
            <div class="flex space-x-4">
                @if ($tempat->kategori_sewa == 'per hari')
                    <a x-data
                        @click.prevent="
                            @if ($selectedLokasi) window.location.href = '{{ route('penyewaan.perhari', ['id_lokasi' => $selectedLokasi]) }}'
                            @else alert('Silakan pilih lokasi terlebih dahulu!') @endif
                        "
                        href="#"
                        class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition shadow-md">
                        Pesan
                    </a>
                @else
                    <a x-data
                        @click.prevent="
                            @if ($selectedLokasi) window.location.href = '{{ route('penyewaan.perjam', ['id_lokasi' => $selectedLokasi]) }}'
                            @else alert('Silakan pilih lokasi terlebih dahulu!') @endif
                        "
                        href="#"
                        class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition shadow-md">
                        Pesan
                    </a>
                @endif
            </div>

            <!-- Ulasan section - Passing tempat ID ke komponen ulasan -->
            <div class="w-full mt-8 bg-white rounded-lg shadow-md p-6">
                <livewire:ulasan :tempatId="$tempat->id ?? null" :wire:key="'ulasan-section-' . ($tempat->id ?? 'none')" />
            </div>
        </div>
    </div>
</div>
