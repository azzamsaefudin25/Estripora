<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;

class PenyewaanPerJam extends Component
{
    public $id_lokasi;
    public $tanggal;
    public $jam_tersedia = [];
    public $jam_dipesan = [];
    public $jam_dipilih = [];

    public function mount($id_lokasi)
    {
        $this->id_lokasi = $id_lokasi;
    }

    public function updatedTanggal()
    {
        $this->cekKetersediaanJam();
    }

    public function cekKetersediaanJam()
{
    if (!$this->tanggal) {
        session()->flash('error', 'Silakan pilih tanggal terlebih dahulu.');
        return;
    }

    $this->jam_tersedia = range(8, 22); // Reset daftar jam
    $this->jam_dipesan = [];

    $penyewaan = Penyewaan::where('id_lokasi', $this->id_lokasi)
        ->whereDate('tgl_booking', $this->tanggal)
        ->get();

    foreach ($penyewaan as $sewa) {
        if ($sewa->penyewaan_per_jam) {
            $jam_dipesan = json_decode($sewa->penyewaan_per_jam, true);
            $this->jam_dipesan = array_merge($this->jam_dipesan, $jam_dipesan);
        }
    }

    $this->jam_tersedia = array_diff($this->jam_tersedia, $this->jam_dipesan);

    // Paksa Livewire untuk memperbarui tampilan
    $this->dispatch('refresh');
}


    public function pilihJam($jam)
    {
        if (in_array($jam, $this->jam_dipesan)) return;

        if (in_array($jam, $this->jam_dipilih)) {
            $this->jam_dipilih = array_diff($this->jam_dipilih, [$jam]);
        } else {
            $this->jam_dipilih[] = $jam;
        }
    }

    public function simpanPenyewaan()
    {
        $user = Auth::user();

        if (!$user) {
            session()->flash('error', 'Silakan login terlebih dahulu.');
            return redirect()->route('login');
        }

        if (empty($this->jam_dipilih) || !$this->tanggal) {
            session()->flash('error', 'Silakan pilih tanggal dan jam terlebih dahulu.');
            return;
        }

        Penyewaan::create([
            'id_user' => $user->id,
            'id_lokasi' => $this->id_lokasi,
            'kategori_sewa' => 'per jam',
            'tgl_booking' => $this->tanggal,
            'penyewaan_per_jam' => json_encode($this->jam_dipilih),
            'total_durasi' => count($this->jam_dipilih),
            'tarif' => 50000,
            'sub_total' => count($this->jam_dipilih) * 50000,
            'status' => 'Pending',
        ]);

        session()->flash('success', 'Pemesanan berhasil!');
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.penyewaan-per-jam');
    }
}
