<div>
    @if (session()->has('message'))
        <div class="bg-green-200 text-green-800 p-2 rounded mb-4">
            {{ session('message') }}
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
                @foreach(\App\Models\Penyewaan::with(['lokasi.tempat'])
                    ->where('status', 'Confirmed')
                    ->whereHas('user', function($query) {
                        $query->where('email', auth()->user()->email);
                    })
                    ->get() as $penyewaan)

                    @php
                        $detailText = '';
                        if ($penyewaan->kategori_sewa == 'per jam') {
                            foreach ($penyewaan->penyewaan_per_jam as $jam) {
                                $detailText .= "\n - {$jam['tgl_mulai']} | {$jam['jam_mulai']}-{$jam['jam_selesai']}";
                            }
                        } else {
                            foreach ($penyewaan->penyewaan_per_hari as $hari) {
                                $detailText .= "\n - {$hari['tgl_mulai']}" . ($hari['tgl_mulai'] != $hari['tgl_selesai'] ? " s/d {$hari['tgl_selesai']}" : '');
                            }
                        }
                    @endphp

                    <option value="{{ $penyewaan->id_penyewaan }}">
                        {!! nl2br(
                            $penyewaan->lokasi->tempat->nama . ' - ' .
                            $penyewaan->lokasi->nama_lokasi . ' - ' .
                            $penyewaan->kategori_sewa . ' - ' .
                            $detailText
                        ) !!}
                    </option>
                @endforeach
            </select>
            @error('id_penyewaan') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <!-- Keluhan -->
        <div class="col-span-1">
            <label for="keluhan" class="block font-semibold">Keluhan:</label>
             <textarea wire:model="keluhan" class="w-full border p-2 rounded h-[84px] resize-none"></textarea>
            @error('keluhan') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <!-- Upload Foto -->
        @for ($i = 1; $i <= 3; $i++)
            @php $fotoVar = 'foto' . ($i == 1 ? '' : $i); @endphp

            <div>
                <label for="{{ $fotoVar }}" class="block font-semibold">Upload Foto {{ $i }}:</label>
                <input type="file" wire:model="{{ $fotoVar }}" accept="image/*" class="border p-2 rounded w-full">
                @error($fotoVar) <span class="text-red-500">{{ $message }}</span> @enderror

                <div wire:loading wire:target="{{ $fotoVar }}" class="text-blue-600 font-semibold">
                    Mengambil preview foto...
                </div>

                @if ($this->$fotoVar)
                    <div class="relative w-fit mt-2">
                        <img src="{{ $this->$fotoVar->temporaryUrl() }}" class="w-64 h-64 object-cover rounded shadow border">

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
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Kirim Laporan
            </button>
        </div>
    </form>


    
    <hr class="my-6">
    <!-- Riwayat Laporan -->
    <div class="mt-6 max-h-[400px] overflow-y-auto pr-2">
        <h2 class="text-xl font-bold mb-2">Riwayat Laporan</h2>

        @if ($laporanSebelumnya->isEmpty())
            <p class="text-gray-500">Anda belum pernah membuat laporan.</p>
        @else
            <div class="space-y-4">
                @foreach ($laporanSebelumnya as $lapor)
                    <div class="border rounded p-4 shadow bg-white">
                        <p><span class="font-semibold">ID Penyewaan:</span> {{ $lapor->id_penyewaan }}</p>
                        <p><span class="font-semibold">Keluhan:</span> {{ $lapor->keluhan }}</p>
                        <div class="mt-2">
                            <span class="font-semibold">Foto:</span>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach (['foto', 'foto2', 'foto3'] as $field)
                                    @if ($lapor->$field)
                                        <img src="{{ asset('storage/' . $lapor->$field) }}" class="w-24 h-24 object-cover rounded border" alt="Foto">
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <button wire:click="editLaporan({{ $lapor->id }})"
                            class="mt-2 inline-block bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 text-sm">
                            Edit
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- FORM EDIT LAPORAN -->
    @if ($showEditModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-2xl relative overflow-y-auto max-h-[90vh]">
                <button wire:click="closeEditModal" class="absolute top-2 right-2 text-gray-600 hover:text-gray-800 text-xl">&times;</button>

                <h2 class="text-xl font-bold mb-4">Edit Laporan</h2>

                <form wire:submit.prevent="updateLaporan" class="space-y-4">

                    <!-- Email -->
                    <input type="hidden" wire:model.defer="email">

                    <!-- Pilih ID Penyewaan -->
                    <div>
                        <label for="id_penyewaan" class="block font-semibold">ID Penyewaan:</label>
                        <select wire:model="id_penyewaan" required class="border p-2 rounded w-full">
                            <option value="">Pilih Penyewaan</option>
                            @foreach(\App\Models\Penyewaan::with(['lokasi.tempat'])
                                ->where('status', 'Confirmed')
                                ->whereHas('user', function($query) {
                                    $query->where('email', auth()->user()->email);
                                })
                                ->get() as $penyewaan)

                                @php
                                    $detailText = '';
                                    if ($penyewaan->kategori_sewa == 'per jam') {
                                        foreach ($penyewaan->penyewaan_per_jam as $jam) {
                                            $detailText .= "\n - {$jam['tgl_mulai']} | {$jam['jam_mulai']}-{$jam['jam_selesai']}";
                                        }
                                    } else {
                                        foreach ($penyewaan->penyewaan_per_hari as $hari) {
                                            $detailText .= "\n - {$hari['tgl_mulai']}" . ($hari['tgl_mulai'] != $hari['tgl_selesai'] ? " s/d {$hari['tgl_selesai']}" : '');
                                        }
                                    }
                                @endphp

                                <option value="{{ $penyewaan->id_penyewaan }}">
                                    {!! nl2br(
                                        $penyewaan->lokasi->tempat->nama . ' - ' .
                                        $penyewaan->lokasi->nama_lokasi . ' - ' .
                                        $penyewaan->kategori_sewa . ' - ' .
                                        $detailText
                                    ) !!}
                                </option>
                            @endforeach
                        </select>
                        @error('id_penyewaan') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Keluhan -->
                    <div>
                        <label for="keluhan" class="block font-semibold">Keluhan:</label>
                        <textarea wire:model="keluhan" required class="border p-2 rounded w-full"></textarea>
                        @error('keluhan') <span class="text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Upload Foto -->
                    @for ($i = 1; $i <= 3; $i++)
                        @php
                            $fotoVar = 'foto' . ($i == 1 ? '' : $i);
                            $fotoLamaVar = 'fotoLama' . ($i == 1 ? '' : $i);
                        @endphp

                        <div>
                            <label for="{{ $fotoVar }}" class="block font-semibold">Upload Foto {{ $i }}:</label>
                            <input type="file" wire:model="{{ $fotoVar }}" accept="image/*" class="border p-2 rounded w-full">
                            @error($fotoVar) <span class="text-red-500">{{ $message }}</span> @enderror

                            <div wire:loading wire:target="{{ $fotoVar }}" class="text-blue-600 font-semibold">
                                Mengambil preview foto...
                            </div>

                            {{-- Preview baru --}}
                            @if ($this->$fotoVar)
                                <div class="mt-2 relative">
                                    <img src="{{ $this->$fotoVar->temporaryUrl() }}" class="w-32 h-32 object-cover rounded border">
                                </div>
                            @endif

                            {{-- Foto lama --}}
                            @if ($this->$fotoLamaVar)
                                <div class="mt-2 relative">
                                    <img src="{{ asset('storage/' . $this->$fotoLamaVar) }}" class="w-32 h-32 object-cover rounded border">
                                    <button type="button" wire:click="removeFotoLama({{ $i }})"
                                        class="absolute top-1 right-1 bg-red-600 text-white px-2 py-1 rounded-full text-sm">
                                        Hapus
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endfor

                    <!-- Tombol -->
                    <div class="flex items-center space-x-2">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Simpan Perubahan
                        </button>
                        <button type="button" wire:click="closeEditModal" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


    <script>
        window.addEventListener('scrollToForm', () => {
            const form = document.querySelector('form');
            if (form) {
                form.scrollIntoView({ behavior: 'smooth' });
            }
        });
    </script>

</div>
