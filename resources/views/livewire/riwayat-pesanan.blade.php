<div class="container mx-auto py-6 px-4">
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Riwayat Pesanan</h1>

    <!-- Daftar Riwayat Pesanan -->
    @if ($riwayatPesanan->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500">Belum ada riwayat pesanan</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($riwayatPesanan as $pesanan)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Image Section -->
                    @if ($pesanan->lokasi && $pesanan->lokasi->tempat && $pesanan->lokasi->tempat->image)
                        <div class="w-full h-40 overflow-hidden">
                            <img src="{{ asset('storage/' . $pesanan->lokasi->tempat->image) }}"
                                class="w-full h-full object-cover" alt="{{ $pesanan->lokasi->tempat->nama }}">
                        </div>
                    @else
                        <div class="w-full h-40 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-400">Tidak ada gambar</span>
                        </div>
                    @endif

                    <div class="p-4 border-b">
                        <div class="flex justify-between items-start">
                            <div>
                                <!-- Tempat name -->
                                @if ($pesanan->lokasi && $pesanan->lokasi->tempat)
                                    <h3 class="font-semibold text-lg">{{ $pesanan->lokasi->tempat->nama }}</h3>
                                    <p class="text-sm text-gray-600">{{ $pesanan->lokasi->nama_lokasi }}</p>
                                @elseif ($pesanan->lokasi)
                                    <h3 class="font-semibold text-lg">{{ $pesanan->lokasi->nama_lokasi }}</h3>
                                @else
                                    <h3 class="font-semibold text-lg">Lokasi</h3>
                                @endif
                                <p class="text-sm text-gray-500">
                                    {{ date('d M Y', strtotime($pesanan->tgl_booking)) }}</p>
                            </div>
                            <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">
                                Lunas
                            </span>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="mb-3">
                            <p class="text-sm text-gray-600">Kategori: {{ ucfirst($pesanan->kategori_sewa) }}</p>
                            <p class="text-sm text-gray-600">Durasi: {{ $pesanan->total_durasi }}
                                {{ $pesanan->kategori_sewa == 'per jam' ? 'jam' : 'hari' }}</p>
                            <p class="text-sm font-semibold text-gray-700 mt-1">Total: Rp
                                {{ number_format($pesanan->sub_total, 0, ',', '.') }}</p>
                        </div>

                        <div class="mt-4 flex justify-end">
                            @if ($pesanan->ulasan)
                                <button wire:click="bukaModalEditUlasan({{ $pesanan->id_penyewaan }})"
                                    class="px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    Edit Ulasan
                                </button>
                            @else
                                <button wire:click="bukaModalUlasan({{ $pesanan->id_penyewaan }})"
                                    class="px-4 py-2 bg-green-500 text-white text-sm font-medium rounded hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                                    Beri Ulasan
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal Ulasan -->
    @if ($showUlasanModal)
        <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)" x-show="show"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-90"
                x-transition:enter-end="opacity-100 transform scale-100"class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">{{ $isEditing ? 'Edit Ulasan' : 'Beri Ulasan' }}</h3>
                    <button type="button" wire:click="tutupModalUlasan" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="simpanUlasan">
                    <!-- Rating -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                        <div class="flex items-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" wire:click="setRating({{ $i }})"
                                    class="focus:outline-none">
                                    <svg class="w-8 h-8 {{ $i <= $rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                        </path>
                                    </svg>
                                </button>
                            @endfor
                        </div>
                        @error('rating')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Ulasan -->
                    <div class="mb-4">
                        <label for="ulasan" class="block text-sm font-medium text-gray-700 mb-2">Ulasan</label>
                        <textarea id="ulasan" wire:model="ulasan" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Bagikan pengalaman Anda...">{{ $ulasan }}</textarea>
                        @error('ulasan')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="button" wire:click="tutupModalUlasan"
                            class="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {{ $isEditing ? 'Update Ulasan' : 'Kirim Ulasan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
