<div class="flex flex-col relative">
    <div class="flex-grow flex flex-col items-center p-6">
        <!-- Tombol Kembali dan Keranjang -->
        <div class="absolute top-4 left-4 flex space-x-2">
            <a href="{{ route('dashboard') }}"
                class="bg-gray-700 text-white p-3 rounded-full hover:bg-gray-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                </svg>
            </a>
            <a href="{{ route('keranjang') }}"
                class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-800 transition relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                </svg>
                @if (session()->has('keranjang') && count(session('keranjang')) > 0)
                    <span
                        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                        {{ count(session('keranjang')) }}
                    </span>
                @endif
            </a>
        </div>

        <h1 class="text-3xl font-bold mb-4">Penyewaan Per Jam</h1>
        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show"
                class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r shadow-md relative">
                <div class="flex items-center justify-between">
                    <p class="text-green-800 text-sm font-medium">
                        {{ session('success') }}
                    </p>
                    <button @click="show = false"
                        class="text-green-400 hover:opacity-75 transition-opacity duration-150">
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
        @if ($lokasi)
            <div class="mb-6 bg-white p-4 rounded-lg shadow-md w-full max-w-2xl">
                <h2 class="text-xl font-semibold mb-2">{{ $lokasi->nama_lokasi }}</h2>
                <p>Tarif per jam: Rp {{ number_format($lokasi->tarif, 0, ',', '.') }}</p>
            </div>
        @endif

        <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-2xl">
            <!-- Bagian yang dimodifikasi untuk integrasi kalender -->
            <div class="mt-6">
                <div class="bg-white p-4 rounded-lg shadow-sm mb-4">
                    <h3 class="text-lg font-semibold mb-2">Tanggal Yang Sudah Di-booking</h3>
                    <p class="text-sm text-gray-600 mb-3">Berikut adalah tanggal-tanggal yang sudah dipesan untuk lokasi
                        ini:</p>
                    @livewire('kalenderperjam', ['locationId' => $lokasi->id_lokasi])
                </div>
            </div>
            <form wire:submit.prevent="simpanKeKeranjang">
                <!-- Hour Range Repeater -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-4">Pilih Jadwal Jam</h3>

                    @foreach ($hourRanges as $index => $range)
                        <div class="bg-gray-50 p-4 rounded-md mb-4 border border-gray-200">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="font-medium">Jadwal {{ $index + 1 }}</h4>
                                @if (count($hourRanges) > 1)
                                    <button type="button" wire:click="removeHourRange({{ $index }})"
                                        class="text-red-500 hover:text-red-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-1">Tanggal</label>
                                    <input type="date" wire:model="hourRanges.{{ $index }}.date"
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                        min="{{ now()->format('Y-m-d') }}"
                                        wire:change="updateHourAvailability({{ $index }})">
                                    @error("hourRanges.{$index}.date")
                                        <span class="text-red-600 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-medium mb-1">Jam Mulai</label>
                                <select wire:model="hourRanges.{{ $index }}.startHour"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    wire:change="updateHourRange">
                                    <option value="">Pilih Jam Mulai</option>
                                    @foreach ($availableHours[$index] ?? [] as $hour)
                                        <option value="{{ $hour }}">{{ sprintf('%02d:00', $hour) }}</option>
                                    @endforeach
                                </select>
                                @error("hourRanges.{$index}.startHour")
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-1">Jam Selesai</label>
                                <select wire:model="hourRanges.{{ $index }}.endHour"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                                    wire:change="updateHourRange">
                                    <option value="">Pilih Jam Selesai</option>
                                    @foreach ($endHoursOptions[$index] ?? [] as $hour)
                                        <option value="{{ $hour }}">{{ sprintf('%02d:00', $hour) }}</option>
                                    @endforeach
                                </select>
                                @error("hourRanges.{$index}.endHour")
                                    <span class="text-red-600 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <button type="button" wire:click="addHourRange"
                        class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 flex items-center transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Jadwal Jam
                    </button>
                </div>

                <!-- Deskripsi -->
                <div class="mb-6">
                    <label for="deskripsi" class="block text-gray-700 font-medium mb-2">Deskripsi (opsional)</label>
                    <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"></textarea>
                </div>

                <!-- Summary -->
                <div class="bg-gray-50 p-4 rounded-md mb-6">
                    <h3 class="font-semibold mb-2">Ringkasan Pemesanan</h3>
                    <div class="flex justify-between mb-1">
                        <span>Total Jam</span>
                        <span>{{ $totalJam }} jam</span>
                    </div>
                    <div class="flex justify-between font-medium text-lg">
                        <span>Total Biaya</span>
                        <span>Rp {{ number_format($subTotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit"
                    class="w-full px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                    Tambahkan ke Keranjang
                </button>
            </form>
        </div>
    </div>
</div>
