<?php

namespace App\Livewire;

use App\Models\Ulasan;
use Livewire\Component;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RiwayatPesanan extends Component
{
    // Properties untuk modal ulasan
    public $showUlasanModal = false;
    public $penyewaanId = null;
    public $ulasan = '';
    public $rating = 0;
    public $ulasanId = null;
    public $isEditing = false;

    protected $rules = [
        'rating' => 'required|numeric|min:1|max:5',
    ];

    protected $messages = [
        'rating.required' => 'Rating harus diberikan',
        'rating.min' => 'Rating minimal 1',
        'rating.max' => 'Rating maksimal 5',
    ];

    // Tetapkan listener untuk menangani event dari Alpine.js
    protected $listeners = ['closeModal' => 'tutupModalUlasan'];

    public function render()
    {
        $riwayatPesanan = collect([]);

        // Check if user is authenticated before proceeding
        if (Auth::check()) {
            $user = Auth::user();

            // Mengambil riwayat pesanan berdasarkan id_user pengguna saat ini
            // Tambahkan eager loading untuk tempat melalui lokasi
            $riwayatPesanan = Penyewaan::with(['ulasan', 'lokasi.tempat'])
                ->where('id_user', $user->id)
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('transaksi')
                        ->whereRaw('transaksi.id_penyewaan = penyewaan.id_penyewaan')
                        ->where('transaksi.status', 'Paid');
                })
                ->latest()
                ->get();
        }

        return view('livewire.riwayat-pesanan', [
            'riwayatPesanan' => $riwayatPesanan
        ]);
    }

    // Buka modal ulasan untuk membuat ulasan baru
    public function bukaModalUlasan($penyewaanId)
    {
        // Reset data terlebih dahulu
        $this->reset(['ulasan', 'rating', 'ulasanId', 'isEditing']);

        // Setel nilai baru
        $this->penyewaanId = $penyewaanId;
        $this->showUlasanModal = true;
    }

    // Buka modal ulasan untuk mengedit ulasan yang sudah ada
    public function bukaModalEditUlasan($penyewaanId)
    {
        // Cari ulasan yang ada
        $ulasan = Ulasan::where('id_penyewaan', $penyewaanId)->first();

        if ($ulasan) {
            // Setel nilai dari ulasan yang sudah ada
            $this->penyewaanId = $penyewaanId;
            $this->ulasan = $ulasan->ulasan;
            $this->rating = $ulasan->rating;
            $this->ulasanId = $ulasan->id_ulasan;
            $this->isEditing = true;
            $this->showUlasanModal = true;
        }
    }

    // Metode untuk mengubah rating
    public function setRating($value)
    {
        $this->rating = $value;
    }

    // Tutup modal ulasan
    public function tutupModalUlasan()
    {
        $this->showUlasanModal = false;
    }

    // Simpan ulasan baru atau update ulasan yang sudah ada
    public function simpanUlasan()
    {
        $this->validate();

        // Verify user is authenticated
        if (!Auth::check()) {
            session()->flash('error', 'Silakan login terlebih dahulu');
            $this->tutupModalUlasan();
            return redirect()->route('login');
        }

        $penyewaan = Penyewaan::find($this->penyewaanId);

        if (!$penyewaan) {
            session()->flash('error', 'Penyewaan tidak ditemukan');
            return redirect()->route('riwayat-pesanan');
        }

        if ($this->isEditing) {
            // Update ulasan yang sudah ada
            $ulasan = Ulasan::find($this->ulasanId);
            if ($ulasan) {
                $ulasan->update([
                    'ulasan' => $this->ulasan,
                    'rating' => $this->rating
                ]);
                session()->flash('success', 'Ulasan berhasil diupdate');
            }
        } else {
            // Buat ulasan baru
            Ulasan::create([
                'id_penyewaan' => $this->penyewaanId,
                'nik' => $penyewaan->nik,
                'ulasan' => $this->ulasan,
                'rating' => $this->rating
            ]);
            session()->flash('success', 'Ulasan berhasil ditambahkan');
        }

        $this->tutupModalUlasan();

        // Redirect kembali ke halaman riwayat pesanan
        return redirect()->route('riwayat');
    }
}
