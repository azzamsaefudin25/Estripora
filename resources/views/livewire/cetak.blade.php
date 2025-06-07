<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto space-y-10">
    
    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-4 p-3 bg-yellow-100 text-yellow-700 rounded">
            {{ session('warning') }}
        </div>
    @endif

    {{-- PERINGATAN UMUM TENTANG BATAS WAKTU --}}
    <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-orange-700">
                    <strong>Penting:</strong> Setiap transaksi yang dibuat harus dibayar dalam waktu <strong>2 jam</strong>. 
                    Transaksi yang tidak dibayar akan otomatis kadaluarsa dan dihapus dari sistem.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- KOTAK KIRI: METODE PEMBAYARAN --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Metode Pembayaran</h2>

            <div class="mb-4">
                <label for="metodePembayaran" class="block text-gray-700 font-semibold mb-1">Pilih Metode</label>
                <select id="metodePembayaran" wire:model="metodePembayaran"
                    class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih --</option>
                    <option value="ATM">ATM</option>
                    <option value="Mobile Banking">Mobile Banking</option>
                    <option value="Teller Bank">Teller Bank</option>
                </select>
                @error('metodePembayaran') 
                    <span class="text-red-600 text-sm">{{ $message }}</span> 
                @enderror
            </div>

            <p class="text-sm text-gray-500">Silakan pilih metode pembayaran sebelum membuat ID Billing.</p>
        </div>

        {{-- KOTAK KANAN: BUAT ID BILLING --}}
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Buat ID Billing</h2>

            <div class="flex flex-col space-y-4">
                <button wire:click="generateBilling"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Buat ID Billing
                </button>

                @if($idBilling)
                <div class="p-3 bg-green-100 text-green-800 text-sm rounded-lg">
                    <div class="flex items-center space-x-2 mb-2">
                        <span>ID Billing: <span class="font-semibold">{{ $idBilling }}</span></span>
                        <button onclick="copyToClipboard('{{ $idBilling }}')" class="bg-green-300 hover:bg-green-500 text-white p-1 rounded" title="Salin">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75" />
                            </svg>
                        </button>
                        <span id="copySuccess" class="text-green-700 text-xs hidden">Disalin!</span>
                    </div>
                    <div class="text-xs text-orange-600 font-medium">
                        ⏰ Berlaku sampai: 2 jam dari sekarang
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- KONTAINER UPLOAD BUKTI BAYAR --}}
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800 flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <span>Upload Bukti Bayar</span>
        </h2>

        <form wire:submit.prevent="uploadBuktiBayar" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Input ID Billing --}}
                <div>
                    <label for="idBillingUpload" class="block text-gray-700 font-semibold mb-2">ID Billing</label>
                    <input 
                        type="text" 
                        id="idBillingUpload"
                        wire:model="idBillingUpload"
                        placeholder="Masukkan ID Billing (contoh: BILL-ABC12345)"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono"
                        {{ $isUploading ? 'disabled' : '' }}
                    >
                    @error('idBillingUpload') 
                        <span class="text-red-600 text-sm">{{ $message }}</span> 
                    @enderror
                </div>

                {{-- Input File Upload --}}
                <div>
                    <label for="buktiBayar" class="block text-gray-700 font-semibold mb-2">File Bukti Bayar</label>
                    <input 
                        type="file" 
                        id="buktiBayar"
                        wire:model="buktiBayar"
                        accept=".jpg,.jpeg,.png,.pdf"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        {{ $isUploading ? 'disabled' : '' }}
                    >
                    @error('buktiBayar') 
                        <span class="text-red-600 text-sm">{{ $message }}</span> 
                    @enderror
                </div>
            </div>

            {{-- Info dan Tombol Submit --}}
            <div class="flex flex-col md:flex-row md:items-end md:justify-between space-y-4 md:space-y-0">
                <div class="text-sm text-gray-600">
                    <p class="text-yellow-600"><strong>Catatan:</strong> Silahkan lakukan pembayaran melalui transfer bank ke rekening berikut : </p>
                    <div class="text-yellow-600 list-disc list-inside">
                        <p><strong>Bank:</strong> Mandiri</p>
                        <p><strong>Nama Penerima:</strong> Irfan</p>
                        <p><strong>No. Rekening:</strong> 2222 2222 2222</p>
                    </div>
                    <p><strong>Format yang didukung:</strong> JPG, JPEG, PNG, PDF</p>
                    <p><strong>Ukuran maksimal:</strong> 5MB</p>
                </div>

                <button 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-3 px-6 rounded-lg flex items-center space-x-2 transition-colors duration-200"
                    {{ $isUploading ? 'disabled' : '' }}
                >
                    @if($isUploading)
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mengupload...</span>
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span>Upload Bukti Bayar</span>
                    @endif
                </button>
            </div>
        </form>
    </div>
    
    {{-- DAFTAR TRANSAKSI --}}
    <div class="bg-white rounded-2xl shadow-lg p-6">
        <h2 class="text-xl font-bold mb-4 text-gray-800">Daftar Transaksi</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-100 text-gray-700">
                    <tr>
                        <th class="px-4 py-2 text-center">
                            <input type="checkbox" wire:model="selectAll" />
                        </th>
                        <th class="px-4 py-2">NIK</th>
                        <th class="px-4 py-2">ID Billing</th>
                        <th class="px-4 py-2">Tanggal Booking</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Per Hari</th>
                        <th class="px-4 py-2">Per Jam</th>
                        <th class="px-4 py-2">Tarif</th>
                        <th class="px-4 py-2">Sub Total</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Bukti Bayar</th>
                        <th class="px-4 py-2">Aksi</th>
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
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 text-center">
                                <input type="checkbox" wire:model="selectedTransaksis" value="{{ $transaksi->id }}">
                            </td>
                            <td class="px-4 py-2">{{ $transaksi->nik ?? '-' }}</td>
                            <td class="px-4 py-2 font-mono">{{ $transaksi->id_billing }}</td>
                            <td class="px-4 py-2">{{ \Carbon\Carbon::parse($transaksi->tgl_booking)->format('d M Y') }}</td>
                            <td class="px-4 py-2">{{ $penyewaan->kategori_sewa }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if(!empty($perHari) && is_array($perHari))
                                    {{ \Carbon\Carbon::parse($perHari[0]['tgl_mulai'])->format('d M Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($perHari[0]['tgl_selesai'])->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                @if(!empty($perJam) && is_array($perJam))
                                    @foreach($perJam as $jam)
                                        {{ \Carbon\Carbon::parse($jam['tgl_mulai'])->format('d M Y') }}
                                        ({{ \Carbon\Carbon::parse($jam['jam_mulai'])->format('H:i') }} - {{ \Carbon\Carbon::parse($jam['jam_selesai'])->format('H:i') }})<br>
                                    @endforeach
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-2">Rp {{ number_format($transaksi->tarif, 0, ',', '.') }}</td>
                            <td class="px-4 py-2 font-bold text-blue-600">Rp {{ number_format($transaksi->sub_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-2">
                                @if($transaksi->status === 'Pending')
                                    <span class="text-yellow-600 font-semibold">{{ $transaksi->status }}</span>
                                @elseif($transaksi->status === 'Paid')
                                    <span class="text-green-600 font-semibold">{{ $transaksi->status }}</span>
                                @elseif($transaksi->status === 'Failed')
                                    <span class="text-red-600 font-semibold">{{ $transaksi->status }}</span>
                                @else
                                    {{ $transaksi->status }}
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if($transaksi->bukti_bayar)
                                    <a href="{{ Storage::url($transaksi->bukti_bayar) }}" 
                                       target="_blank" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm"
                                       title="Lihat Bukti Bayar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Lihat
                                    </a>
                                @else
                                    <span class="text-gray-400 text-sm">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center space-x-2">
                                {{-- Tombol Hapus --}}
                                <button
                                    wire:click.prevent="hapusTransaksi({{ $transaksi->id }})"
                                    onclick="return confirm('Yakin ingin menghapus transaksi ini?')"
                                    title="Hapus"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-gray-500 py-4">Tidak ada data transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TOMBOL CETAK PDF --}}
        <div class="mt-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                @if(count($selectedTransaksis) > 0)
                    {{ count($selectedTransaksis) }} transaksi dipilih
                @else
                    Pilih transaksi untuk dicetak
                @endif
            </div>
            
            <button wire:click="cetakPDF"
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded-lg flex items-center space-x-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Cetak PDF Transaksi Terpilih</span>
            </button>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const el = document.getElementById('copySuccess');
        if (el) {
            el.classList.remove('hidden');
            setTimeout(() => el.classList.add('hidden'), 1500);
        }
    });
}
</script>