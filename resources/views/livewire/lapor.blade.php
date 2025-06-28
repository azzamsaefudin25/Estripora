<div>
    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show"
            class="bg-red-50 border-l-4 border-red-400 p-3 sm:p-4 rounded-r shadow-md relative mb-4">
            <div class="flex items-center justify-between">
                <p class="text-red-800 text-xs sm:text-sm font-medium pr-2">
                    {{ session('error') }}
                </p>
                <button @click="show = false"
                    class="text-red-400 hover:opacity-75 transition-opacity duration-150 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- FORM SUBMIT LAPORAN BARU -->
    <form wire:submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Email (hidden) -->
        <input type="hidden" wire:model.defer="email">

        <!-- Pilih id Penyewaan -->
        <div class="col-span-2">
            <label for="id_penyewaan" class="block font-semibold">ID Penyewaan:</label>
            <select wire:model="id_penyewaan" required class="w-full border p-2 rounded">
                <option value="">Pilih Penyewaan</option>
                @foreach (\App\Models\Penyewaan::with(['lokasi.tempat'])->where('status', 'Confirmed')->whereHas('user', function ($query) {
            $query->where('email', auth()->user()->email);
        })->get() as $penyewaan)
                    @php
                        $detailText = '';
                        if ($penyewaan->kategori_sewa == 'per jam') {
                            foreach ($penyewaan->penyewaan_per_jam as $jam) {
                                $detailText .= "\n - {$jam['tgl_mulai']} | {$jam['jam_mulai']}-{$jam['jam_selesai']}";
                            }
                        } else {
                            foreach ($penyewaan->penyewaan_per_hari as $hari) {
                                $detailText .=
                                    "\n - {$hari['tgl_mulai']}" .
                                    ($hari['tgl_mulai'] != $hari['tgl_selesai'] ? " s/d {$hari['tgl_selesai']}" : '');
                            }
                        }
                    @endphp

                    <option value="{{ $penyewaan->id_penyewaan }}">
                        {!! nl2br(
                            $penyewaan->lokasi->tempat->nama .
                                ' - ' .
                                $penyewaan->lokasi->nama_lokasi .
                                ' - ' .
                                $penyewaan->kategori_sewa .
                                ' - ' .
                                $detailText,
                        ) !!}
                    </option>
                @endforeach
            </select>
            @error('id_penyewaan')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <!-- Keluhan -->
        <div class="col-span-1">
            <label for="keluhan" class="block font-semibold">Keluhan:</label>
            <textarea wire:model="keluhan" class="w-full border p-2 rounded h-[84px] resize-none"></textarea>
            @error('keluhan')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <!-- Upload Foto -->
        @for ($i = 1; $i <= 3; $i++)
            @php $fotoVar = 'foto' . ($i == 1 ? '' : $i); @endphp

            <div>
                <label for="{{ $fotoVar }}" class="block font-semibold">Upload Foto {{ $i }}:</label>
                <input type="file" wire:model="{{ $fotoVar }}" accept=".jpg,.jpeg,.png"
                    class="border p-2 rounded w-full" data-validate="image">
                @error($fotoVar)
                    <span class="text-red-500">{{ $message }}</span>
                @enderror

                <div wire:loading wire:target="{{ $fotoVar }}" class="text-blue-600 font-semibold">
                    Upload foto sedang berlangsung, harap tunggu sampai selesai.
                </div>

                @if ($this->$fotoVar)
                    <div class="relative w-fit mt-2">
                        <img src="{{ $this->$fotoVar->temporaryUrl() }}"
                            class="w-64 h-64 object-cover rounded shadow border">

                        <button type="button" wire:click="removeFoto('{{ $fotoVar }}')"
                            class="absolute top-2 right-2 bg-red-600 text-white px-2 py-1 text-xs rounded hover:bg-red-700 shadow">
                            Hapus
                        </button>
                    </div>
                @endif
            </div>
        @endfor

        <!-- Tombol Kirim -->
        <div class="col-span-2">
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                wire:loading.attr="disabled" wire:target="foto, foto2, foto3">
                Kirim Laporan
            </button>
        </div>
    </form>



    <hr class="my-6">
    <!-- Riwayat Laporan -->
    <h2 class="text-2xl font-bold mb-4">Riwayat Laporan</h2>
    @if ($laporanSebelumnya->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-6 text-center">
            <p class="text-gray-500">Anda belum pernah membuat laporan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($laporanSebelumnya as $lap)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-4 border-b">
                        <div class="flex justify-between">
                            <div>
                                {{-- Nama tempat & lokasi --}}
                                @if ($lap->penyewaan && $lap->penyewaan->lokasi->tempat)
                                    <h3 class="font-semibold text-lg">{{ $lap->penyewaan->lokasi->tempat->nama }}</h3>
                                    <p class="text-sm text-gray-600">{{ $lap->penyewaan->lokasi->nama_lokasi }}</p>
                                @endif
                                {{-- Tanggal Pesanan --}}
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($lap->penyewaan->tgl_booking)->format('d M Y') }}
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="p-4">
                        {{-- Kategori & Durasi --}}
                        <p class="text-sm text-gray-600">Kategori: {{ ucfirst($lap->penyewaan->kategori_sewa) }}</p>
                        <p class="text-sm text-gray-600">Durasi: {{ $lap->penyewaan->total_durasi }}
                            {{ $lap->penyewaan->kategori_sewa == 'per jam' ? 'jam' : 'hari' }}</p>

                        {{-- Keluhan --}}
                        <!--hapus html tag dengan strip_tags-->
                        <p class="text-sm font-semibold text-gray-700 mt-2">
                            Keluhan: {{ strip_tags($lap->keluhan) }}
                        </p>
                    </div>

                    {{-- Foto --}}
                    @if (collect([$lap->foto, $lap->foto2, $lap->foto3])->filter()->isNotEmpty())
                        <div class="p-4 border-t">
                            <div class="grid grid-cols-3 gap-2">
                                @foreach (['foto', 'foto2', 'foto3'] as $f)
                                    @if ($lap->$f)
                                        <img src="{{ asset('storage/' . $lap->$f) }}"
                                            class="w-full h-32 object-cover rounded">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Hapus & Lihat Balasan --}}
                    <div class="p-2 border-t text-right">
                        @if (!$lap->balasan)
                            <button
                                onclick="if(!confirm('Anda yakin ingin menghapus laporan ini?')) return event.stopImmediatePropagation();"
                                wire:click="deleteLaporan({{ $lap->id_lapor }})"
                                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm inline-block">
                                Hapus
                            </button>
                        @else
                            <button wire:click="viewBalasan('{{ json_encode($lap->balasan) }}')"
                                class="px-4 py-2 bg-blue-500 text-white rounded">
                                Lihat Balasan
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal Balasan -->
    <!--hapus html tag dengan strip_tags-->
    @if ($showBalasanPanel)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-lg w-full">
                <h3 class="text-xl font-semibold mb-4">Balasan</h3>
                <div class="border p-4 rounded mb-4 whitespace-pre-wrap">{{ strip_tags($currentBalasan) }}</div>
                <button wire:click="closeBalasan" class="px-4 py-2 bg-gray-500 text-white rounded">Tutup</button>
            </div>
        </div>
    @endif

</div>

<!-- script tolak ukuran file foto lebih dari 5MB-->
<script>
    document.querySelectorAll('input[data-validate="image"]').forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!allowedTypes.includes(file.type)) {
                alert('Hanya file JPEG, JPG atau PNG yang diizinkan.');
                this.value = '';
                return;
            }
            if (file.size > maxSize) {
                alert('Maksimum ukuran foto 5 MB.');
                this.value = '';
                return;
            }
        });
    });
</script>
