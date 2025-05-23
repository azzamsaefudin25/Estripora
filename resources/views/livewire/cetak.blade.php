<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-semibold mb-4">Bayar dan Cetak</h1>

        @if (session()->has('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif
{{-- Buat ID Billing --}}
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Buat ID Billing</h2>
            <p class="text-sm text-gray-500">Klik tombol di samping untuk membuat ID billing baru.</p>
        </div>
        <div>
            <button wire:click="generateBilling" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Buat ID Billing
            </button>
        </div>
    </div>

    @if($idBilling)
        <div class="mt-4 p-3 bg-green-100 text-green-800 text-sm rounded-lg flex items-center space-x-2">
            <span>ID Billing berhasil dibuat: <span class="font-semibold">{{ $idBilling }}</span></span>
            <button
                onclick="copyToClipboard('{{ $idBilling }}')"
                class="bg-green-300 hover:bg-green-500 text-white p-1 rounded flex items-center"
                title="Salin ID Billing"
            >
                {{-- Heroicon Clipboard --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5v2a3 3 0 006 0V5" />
                </svg>
            </button>
            <span id="copySuccess" class="text-green-700 text-xs hidden">Disalin!</span>
        </div>
    @endif
</div>

<script>
    function copyToClipboard(text) {
        if (!navigator.clipboard) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                showCopySuccess();
            } catch (err) {
                alert('Gagal menyalin');
            }
            document.body.removeChild(textArea);
            return;
        }
        navigator.clipboard.writeText(text).then(() => {
            showCopySuccess();
        }, () => {
            alert('Gagal menyalin');
        });
    }

    function showCopySuccess() {
        const el = document.getElementById('copySuccess');
        el.classList.remove('hidden');
        setTimeout(() => {
            el.classList.add('hidden');
        }, 1500);
    }
</script>

        {{-- Tabel Transaksi --}}
        <div class="bg-white overflow-x-auto rounded-lg shadow-md p-6 mb-4">
            <table class="w-full table-auto text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="px-4 py-2"></th>
                        <th class="px-4 py-2 text-left font-semibold">NIK</th>
                        <th class="px-4 py-2 text-left font-semibold">ID Billing</th>
                        <th class="px-4 py-2 text-left font-semibold">Tanggal Booking</th>
                        <th class="px-4 py-2 text-left font-semibold">Kategori</th>
                        <th class="px-4 py-2 text-left font-semibold">Penyewaan Per Hari</th>
                        <th class="px-4 py-2 text-left font-semibold">Penyewaan Per Jam</th>
                        <th class="px-4 py-2 text-left font-semibold">Tarif</th>
                        <th class="px-4 py-2 text-left font-semibold">Sub Total</th>
                        <th class="px-4 py-2 text-left font-semibold">Status</th>
                        <th class="px-4 py-2 text-left font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        @php
                            $penyewaan = $transaksi->penyewaan;
                            $perHari = is_string($penyewaan->penyewaan_per_hari ?? '') 
                                        ? json_decode($penyewaan->penyewaan_per_hari, true) 
                                        : ($penyewaan->penyewaan_per_hari ?? []);
                            $perJam = is_string($penyewaan->penyewaan_per_jam ?? '') 
                                        ? json_decode($penyewaan->penyewaan_per_jam, true) 
                                        : ($penyewaan->penyewaan_per_jam ?? []);
                        @endphp
                        <tr class="border-b">
                            <td class="px-4 py-2 text-center">
                                <input type="checkbox" wire:model="selectedTransaksis" value="{{ $transaksi->id }}" />
                            </td>
                            <td class="px-4 py-2">{{ $transaksi->nik }}</td>
                            <td class="px-4 py-2">{{ $transaksi->id_billing }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($transaksi->tgl_booking)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $penyewaan->kategori_sewa ?? '-' }}</td>
                            <td class="px-4 py-2">
                                @if (is_array($perHari) && count($perHari) > 0)
                                    @foreach ($perHari as $item)
                                        {{ \Carbon\Carbon::parse($item['tgl_mulai'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($item['tgl_selesai'])->format('d M Y') }}<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                @if (is_array($perJam) && count($perJam) > 0)
                                    @foreach ($perJam as $item)
                                        {{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai'] ?? '-' }}<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2">Rp{{ number_format($transaksi->tarif, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">Rp{{ number_format($transaksi->sub_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">{{ $transaksi->status }}</td>
                            <td class="px-4 py-2 text-center">
                                <button 
                                    type="button"
                                    onclick="confirmDelete({{ $transaksi->id }})" 
                                    class="text-red-600 hover:text-red-800" 
                                    title="Hapus transaksi"
                                >
                                    {{-- Heroicon Trash --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-2 text-center text-gray-500">Belum ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tombol Cetak PDF (posisi kanan) --}}
        <div class="flex justify-end">
            <button wire:click="cetakPDF" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                Cetak PDF
            </button>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Yakin ingin menghapus transaksi ini?')) {
            @this.hapusTransaksi(id);
        }
    }
</script>
