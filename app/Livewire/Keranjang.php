<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Keranjang extends Component
{
    public $keranjangItems = [];
    public $totalKeseluruhan = 0;

    protected $listeners = ['keranjangUpdated' => 'refreshKeranjang'];

    public function mount()
    {
        $this->refreshKeranjang();
    }

    public function refreshKeranjang()
    {
        $this->keranjangItems = Session::get('keranjang', []);
        $this->hitungTotal();
    }

    public function hitungTotal()
    {
        $this->totalKeseluruhan = 0;
        foreach ($this->keranjangItems as $item) {
            $this->totalKeseluruhan += $item['sub_total'];
        }
    }

    public function hapusItem($index)
    {
        // Ambil keranjang dari session
        $keranjang = Session::get('keranjang', []);

        // Pastikan index valid
        if (isset($keranjang[$index])) {
            // Hapus item dari array
            unset($keranjang[$index]);

            // Re-index array setelah penghapusan
            $keranjang = array_values($keranjang);

            // Simpan kembali ke session
            Session::put('keranjang', $keranjang);

            // Refresh data komponen
            $this->keranjangItems = $keranjang;
            $this->hitungTotal();

            // Perbarui jumlah item di keranjang (untuk badge notifikasi)
            $this->dispatch('keranjangUpdated', count: count($keranjang));

            session()->flash('success', 'Item berhasil dihapus dari keranjang.');
        }
    }

    public function checkout()
    {
        // Cek apakah keranjang kosong
        if (empty($this->keranjangItems)) {
            session()->flash('error', 'Keranjang kosong. Tidak ada yang dapat di-checkout.');
            return;
        }

        // Cek apakah user sudah login
        if (!Auth::check()) {
            // Simpan URL saat ini ke session untuk redirect kembali setelah login
            Session::put('url.intended', route('keranjang'));

            session()->flash('error', 'Silakan login terlebih dahulu untuk melanjutkan checkout.');

            // Redirect ke halaman login
            return redirect()->route('login');
        }

        // Ambil data user termasuk NIK
        $user = User::find(Auth::id());

        // Simpan semua item dari keranjang ke database
        try {
            foreach ($this->keranjangItems as $item) {
                // Siapkan data 
                $penyewaanData = [
                    'id_user' => Auth::id(),
                    'nik' => $user->nik,
                    'id_lokasi' => $item['id_lokasi'],
                    'kategori_sewa' => $item['kategori_sewa'],
                    'tgl_booking' => $item['tgl_booking'],
                    'deskripsi' => $item['deskripsi'],
                    'total_durasi' => $item['total_durasi'],
                    'tarif' => $item['tarif'],
                    'sub_total' => $item['sub_total'],
                    'status' => 'Pending'
                ];

                // Tambahkan data penyewaan per hari atau per jam sesuai kategori
                if ($item['kategori_sewa'] == 'per hari') {
                    $penyewaanData['penyewaan_per_hari'] = $item['penyewaan_per_hari'];
                    $penyewaanData['penyewaan_per_jam'] = null; // Pastikan field lain null
                } elseif ($item['kategori_sewa'] == 'per jam') {
                    $penyewaanData['penyewaan_per_jam'] = $item['penyewaan_per_jam'];
                    $penyewaanData['penyewaan_per_hari'] = null; // Pastikan field lain null
                }

                // Buat record penyewaan
                Penyewaan::create($penyewaanData);
            }

            // Kosongkan keranjang setelah checkout berhasil
            Session::forget('keranjang');

            // Refresh data komponen
            $this->keranjangItems = [];
            $this->totalKeseluruhan = 0;

            // Perbarui jumlah item di keranjang (untuk badge notifikasi)
            $this->dispatch('keranjangUpdated', count: 0);

            session()->flash('success', 'Checkout berhasil! Semua penyewaan telah diproses.');

            return redirect()->route('cetak');
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan saat checkout: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.keranjang');
    }
}
