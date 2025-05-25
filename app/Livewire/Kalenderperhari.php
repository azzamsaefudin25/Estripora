<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use Carbon\Carbon;

class Kalenderperhari extends Component
{
    public $locationId;

    public function mount($locationId = null)
    {
        $this->locationId = $locationId;
    }

    public function render()
    {
        $events = [];

        // Filter penyewaan hanya untuk lokasi yang dipilih dan dengan status Pending atau Confirmed
        $query = Penyewaan::with(['user', 'lokasi'])
            ->whereIn('status', ['Pending', 'Confirmed']);

        if ($this->locationId) {
            $query->where('id_lokasi', $this->locationId);
        }

        $penyewaans = $query->get();

        // Siapkan data untuk kalender
        foreach ($penyewaans as $p) {
            // Untuk penyewaan per hari
            if ($p->kategori_sewa == 'per hari' && !empty($p->penyewaan_per_hari)) {
                // Cek apakah data sudah dalam format array atau masih dalam format JSON string
                $periodeHari = $p->penyewaan_per_hari;

                // Jika masih dalam format JSON string, decode terlebih dahulu
                if (is_string($periodeHari)) {
                    $periodeHari = json_decode($periodeHari, true);
                }

                // Jika hasil decode adalah array atau memang sudah array dari awal
                if (is_array($periodeHari)) {
                    // Coba proses berbagai kemungkinan struktur data
                    $this->prosesStrukturData($events, $p, $periodeHari);
                }
            }
        }

        return view('livewire.kalenderperhari', [
            'events' => $events
        ]);
    }

    // Proses berbagai kemungkinan struktur data
    private function prosesStrukturData(&$events, $penyewaan, $data)
    {
        // Untuk debugging
        // dd($data);

        // Jika struktur data adalah array asosiatif dengan kunci 'tgl_mulai' yang berisi array
        if (isset($data['tgl_mulai']) && is_array($data['tgl_mulai'])) {
            foreach ($data['tgl_mulai'] as $range) {
                if (isset($range['tgl_mulai']) && isset($range['tgl_selesai'])) {
                    $this->tambahEventRentang($events, $penyewaan, $range['tgl_mulai'], $range['tgl_selesai']);
                }
            }
        }
        // Jika struktur data adalah array dengan elemen yang memiliki 'tgl_mulai' dan 'tgl_selesai'
        elseif (isset($data[0]) && is_array($data[0])) {
            foreach ($data as $range) {
                if (isset($range['tgl_mulai']) && isset($range['tgl_selesai'])) {
                    $this->tambahEventRentang($events, $penyewaan, $range['tgl_mulai'], $range['tgl_selesai']);
                }
            }
        }
        // Jika struktur data adalah array asosiatif dengan kunci 'tgl_mulai' dan 'tgl_selesai' langsung
        elseif (isset($data['tgl_mulai']) && isset($data['tgl_selesai'])) {
            $this->tambahEventRentang($events, $penyewaan, $data['tgl_mulai'], $data['tgl_selesai']);
        }
    }

    // Helper function untuk menambahkan event berdasarkan rentang tanggal
    private function tambahEventRentang(&$events, $penyewaan, $tanggalMulai, $tanggalSelesai)
    {
        try {
            $start = Carbon::parse($tanggalMulai);
            $end = Carbon::parse($tanggalSelesai);

            // Tambahkan event untuk setiap hari dalam rentang
            for ($date = clone $start; $date->lte($end); $date->addDay()) {
                $events[] = [
                    'title' => 'Booked',
                    'start' => $date->format('Y-m-d'),
                    'textColor' => '#000000',
                    'backgroundColor' => 'transparent', 
                    'borderColor' => 'transparent', 

                    'extendedProps' => [
                        'tempat' => $penyewaan->lokasi->tempat->nama,
                        'lokasi' => $penyewaan->lokasi->nama_lokasi
                    ]
                ];
            }
        } catch (\Exception $e) {
            // Handle kesalahan parsing tanggal
            // Uncomment untuk debugging
            // dd($e->getMessage(), $tanggalMulai, $tanggalSelesai);
        }
    }
}
