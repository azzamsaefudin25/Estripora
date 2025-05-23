<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;

class Cetak extends Component
{
    public string $idBilling = '';
    public $transaksis = [];
    public $selectedTransaksis = [];

    public function mount()
    {
        $this->loadTransaksis();
    }

    public function generateBilling()
    {
        $this->idBilling = 'BILL-' . strtoupper(Str::random(8));

        Transaksi::create([
            'id_penyewaan' => 1,
            'id_billing' => $this->idBilling,
            'nik' => '1234567890123456',
            'tgl_booking' => now(),
            'detail_penyewaan' => json_encode(['tipe' => 'per hari', 'durasi' => '3 hari']),
            'total_durasi' => 3,
            'tarif' => 150000,
            'sub_total' => 450000,
            'status' => 'Pending',
        ]);

        $this->loadTransaksis();
    }

    public function loadTransaksis()
    {
        $this->transaksis = Transaksi::with('penyewaan')->latest()->get();
    }

    public function hapusTransaksi($id)
    {
        Transaksi::find($id)?->delete();
        $this->loadTransaksis();

        // Optional: hapus dari selected juga
        $this->selectedTransaksis = array_filter($this->selectedTransaksis, fn($val) => $val != $id);
    }

    public function cetakPDF()
    {
        if (empty($this->selectedTransaksis)) {
            session()->flash('error', 'Pilih minimal satu transaksi untuk dicetak!');
            return;
        }

        $transaksis = Transaksi::with('penyewaan')->whereIn('id', $this->selectedTransaksis)->get();

        $pdf = Pdf::loadView('pdf.transaksi', [
            'transaksis' => $transaksis,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'transaksi.pdf');
    }

    public function render()
    {
        return view('livewire.cetak', [
            'transaksis' => $this->transaksis,
        ]);
    }
}
