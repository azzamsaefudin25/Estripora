<div class="min-h-screen flex flex-col items-center p-6 bg-gray-100">

    <h1 class="text-3xl font-bold mb-4">Keranjang Penyewaan</h1>

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show"
            class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r shadow-md relative">
            <div class="flex items-center justify-between">
                <p class="text-green-800 text-sm font-medium">
                    {{ session('success') }}
                </p>
                <button @click="show = false" class="text-green-400 hover:opacity-75 transition-opacity duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show"
            class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r shadow-md relative">
            <div class="flex items-center justify-between">
                <p class="text-red-800 text-sm font-medium">
                    {{ session('error') }}
                </p>
                <button @click="show = false" class="text-red-400 hover:opacity-75 transition-opacity duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if (count($keranjangItems) > 0)
        <div class="w-full max-w-4xl bg-white rounded-lg shadow-md p-6 mb-6">
            <!-- Daftar Item Keranjang -->
            <div class="space-y-4">
                @foreach ($keranjangItems as $index => $item)
                    @if ($item['kategori_sewa'] == 'per hari')
                        <div class="border border-gray-200 rounded-md p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        {{ $item['nama_tempat'] }} - {{ $item['nama_lokasi'] }}
                                    </h3>
                                    <p class="text-gray-500">Kategori: Per Hari</p>
                                    <p class="mt-2">Tanggal Booking:
                                        {{ \Carbon\Carbon::parse($item['tgl_booking'])->format('d M Y') }}</p>

                                    <div class="mt-2">
                                        <p class="font-medium">Rentang Tanggal:</p>
                                        <ul class="list-disc list-inside ml-2">
                                            @foreach ($item['penyewaan_per_hari'] as $range)
                                                <li>
                                                    {{ \Carbon\Carbon::parse($range['tgl_mulai'])->format('d M Y') }} -
                                                    {{ \Carbon\Carbon::parse($range['tgl_selesai'])->format('d M Y') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    @if (!empty($item['deskripsi']))
                                        <p class="mt-2"><span class="font-medium">Deskripsi:</span>
                                            {{ $item['deskripsi'] }}</p>
                                    @endif

                                    <div class="mt-3">
                                        <p>Total Durasi: {{ $item['total_durasi'] }} hari</p>
                                        <p>Tarif: Rp {{ number_format($item['tarif'], 0, ',', '.') }}/hari</p>
                                        <p class="font-semibold">Subtotal: Rp
                                            {{ number_format($item['sub_total'], 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <button wire:click="hapusItem({{ $index }})"
                                    class="text-red-500 hover:text-red-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                    <!-- Kode sebelumnya tetap sama -->

                    <!-- Tambahkan ini di dalam foreach loop untuk menampilkan item penyewaan per jam -->
                    @if ($item['kategori_sewa'] == 'per jam')
                        <div class="border border-gray-200 rounded-md p-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        {{ $item['nama_tempat'] }} - {{ $item['nama_lokasi'] }}
                                    </h3>
                                    <p class="text-gray-500">Kategori: Per Jam</p>
                                    <p class="mt-2">Tanggal Booking:
                                        {{ \Carbon\Carbon::parse($item['tgl_booking'])->format('d M Y') }}</p>

                                    <div class="mt-2">
                                        <p class="font-medium">Jadwal:</p>
                                        <ul class="list-disc list-inside ml-2">
                                            @foreach ($item['penyewaan_per_jam'] as $range)
                                                <li>
                                                    {{ \Carbon\Carbon::parse($range['tgl_mulai'])->format('d M Y') }},
                                                    {{ substr($range['jam_mulai'], 0, 5) }} -
                                                    {{ substr($range['jam_selesai'], 0, 5) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    @if (!empty($item['deskripsi']))
                                        <p class="mt-2"><span class="font-medium">Deskripsi:</span>
                                            {{ $item['deskripsi'] }}</p>
                                    @endif

                                    <div class="mt-3">
                                        <p>Total Durasi: {{ $item['total_durasi'] }} jam</p>
                                        <p>Tarif: Rp {{ number_format($item['tarif'], 0, ',', '.') }}/jam</p>
                                        <p class="font-semibold">Subtotal: Rp
                                            {{ number_format($item['sub_total'], 0, ',', '.') }}</p>
                                    </div>
                                </div>

                                <button wire:click="hapusItem({{ $index }})"
                                    class="text-red-500 hover:text-red-700 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Total Keseluruhan -->
            <div class="mt-6 border-t border-gray-200 pt-4">
                <div class="flex justify-between text-lg font-semibold">
                    <span>Total Keseluruhan:</span>
                    <span>Rp {{ number_format($totalKeseluruhan, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Tombol Checkout -->
            <div class="mt-6">
                <button wire:click="checkout"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    Checkout
                </button>
            </div>
        </div>
    @else
        <div class="w-full max-w-4xl bg-white rounded-lg shadow-md p-6 mb-6 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                stroke="currentColor" class="w-16 h-16 mx-auto text-gray-400 mb-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            <h2 class="text-xl font-semibold mb-2">Keranjang Kosong</h2>
            <p class="text-gray-600 mb-4">Belum ada penyewaan yang ditambahkan ke keranjang.</p>
            <a href="{{ route('dashboard') }}"
                class="inline-block px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                Lihat Lokasi
            </a>
        </div>
    @endif
</div>
