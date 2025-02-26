<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PenyewaanPerHari extends Component
{
    public $id_lokasi;
    public $tanggal_terpilih = [];
    public $tanggal_dipesan = [];

    public function mount($id_lokasi)
    {
        $this->id_lokasi = $id_lokasi;
        $this->loadTanggalDipesan();
    }

    public function loadTanggalDipesan()
    {
        // Ambil semua tanggal yang sudah dipesan (per hari atau per jam)
        $penyewaan = Penyewaan::where('id_lokasi', $this->id_lokasi)->get();

        // Simpan semua tanggal yang tidak bisa dipilih
        $this->tanggal_dipesan = $penyewaan->pluck('tgl_booking')->toArray();
    }

    public function simpanPenyewaan()
    {

        $user = Auth::user();
        
        if (empty($this->tanggal_terpilih)) {
            session()->flash('error', 'Silakan pilih tanggal terlebih dahulu.');
            return;
        }

        Penyewaan::create([
            'id_user' => $user->id,
            'id_lokasi' => $this->id_lokasi,
            'kategori_sewa' => 'per hari',
            'tgl_booking' => $this->tanggal_terpilih[0], // Simpan tanggal awal
            'penyewaan_per_hari' => json_encode($this->tanggal_terpilih),
            'total_durasi' => count($this->tanggal_terpilih),
            'tarif' => 500000, // Ganti dengan tarif yang sesuai
            'sub_total' => count($this->tanggal_terpilih) * 500000,
            'status' => 'Pending',
        ]);

        session()->flash('success', 'Pemesanan berhasil!');
    }

    public function render()
    {
        return view('livewire.penyewaan-per-hari');
    }
}
