<div class="relative min-h-screen flex flex-col items-center p-6 bg-gray-100">
    <!-- Tombol Navigasi (di ujung kiri atas) -->
    <div class="absolute top-4 left-4 flex gap-2">
        <!-- Tombol Kembali ke Detail Tempat -->
        <a href="{{ route('detail-tempat', ['id' => $id_lokasi]) }}" 
           class="p-2 bg-gray-200 hover:bg-gray-300 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
        </a>

        <!-- Tombol Kembali ke Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="p-2 bg-gray-200 hover:bg-gray-300 rounded-full transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-4">Penyewaan Per Jam</h1>

    <!-- Pilih Tanggal -->
    <div class="mb-4 flex items-center gap-2">
        <input type="date" wire:model="tanggal" class="border p-2 rounded-lg">
        <button wire:click="cekKetersediaanJam" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Search
        </button>
    </div>

    <!-- Jadwal yang tersedia -->
    @if ($jam_tersedia)
        <div class="grid grid-cols-6 gap-2 mb-4">
            @foreach($jam_tersedia as $jam)
                <button 
                    wire:click="pilihJam({{ $jam }})"
                    class="p-3 text-center rounded-lg font-semibold border transition
                        {{ in_array($jam, $jam_dipesan) ? 'bg-gray-400 text-gray-700 cursor-not-allowed' : 
                        (in_array($jam, $jam_dipilih) ? 'bg-blue-700 text-white' : 'bg-white hover:bg-blue-100') }}"
                    {{ in_array($jam, $jam_dipesan) ? 'disabled' : '' }}>
                    {{ sprintf('%02d:00', $jam) }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- Tombol Konfirmasi Pemesanan -->
    <button wire:click="simpanPenyewaan" 
            class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
        Konfirmasi Pemesanan
    </button>

    <!-- Notifikasi -->
    @if (session()->has('error'))
        <p class="text-red-600 mt-2">{{ session('error') }}</p>
    @endif

    @if (session()->has('success'))
        <p class="text-green-600 mt-2">{{ session('success') }}</p>
    @endif
</div>
