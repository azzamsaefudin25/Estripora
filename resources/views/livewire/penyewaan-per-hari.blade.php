<div class="min-h-screen flex flex-col items-center p-6 bg-gray-100">
    <!-- Tombol Kembali -->
    <div class="absolute top-4 left-4 flex space-x-2">
        <a href="{{ route('dashboard') }}" class="bg-gray-700 text-white p-3 rounded-full hover:bg-gray-900 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
            </svg>
        </a>
    </div>

    <h1 class="text-3xl font-bold mb-4">Penyewaan Per Hari</h1>

    <!-- Kalender -->
    <div class="mb-4 w-full max-w-md">
        <label class="text-lg font-semibold">Pilih Rentang Tanggal</label>
        <input type="text" id="calendar" class="border p-2 rounded-lg w-full">
    </div>

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

    <!-- Script Flatpickr -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            flatpickr("#calendar", {
                mode: "range",
                dateFormat: "Y-m-d",
                disable: @json($tanggal_dipesan), // Tanggal yang sudah dipesan akan disable
                onClose: function(selectedDates) {
                    @this.set('tanggal_terpilih', selectedDates.map(date => date.toISOString().split('T')[0]));
                }
            });
        });
    </script>
</div>
