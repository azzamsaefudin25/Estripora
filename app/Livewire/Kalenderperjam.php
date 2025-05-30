<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Penyewaan;
use Carbon\Carbon;

class Kalenderperjam extends Component
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
            // Untuk penyewaan per jam
            if ($p->kategori_sewa == 'per jam' && !empty($p->penyewaan_per_jam)) {
                // Cek apakah data sudah dalam format array atau masih dalam format JSON string
                $periodeJam = $p->penyewaan_per_jam;

                // Jika masih dalam format JSON string, decode terlebih dahulu
                if (is_string($periodeJam)) {
                    $periodeJam = json_decode($periodeJam, true);
                }

                // Jika hasil decode adalah array atau memang sudah array dari awal
                if (is_array($periodeJam)) {
                    // Cek apakah array memiliki indeks numerik (array of objects)
                    // atau indeks asosiatif (single object)
                    if (array_key_exists(0, $periodeJam)) {
                        // Format: [{"jam_mulai":"13:00:00","tgl_mulai":"2025-05-17","jam_selesai":"14:00:00"}, {...}]
                        foreach ($periodeJam as $item) {
                            if (isset($item['jam_mulai']) && isset($item['tgl_mulai']) && isset($item['jam_selesai'])) {
                                $this->tambahEvent($events, $p, $item);
                            }
                        }
                    } else {
                        // Format: {"jam_mulai":"13:00:00","tgl_mulai":"2025-05-17","jam_selesai":"14:00:00"}
                        if (isset($periodeJam['jam_mulai']) && isset($periodeJam['tgl_mulai']) && isset($periodeJam['jam_selesai'])) {
                            $this->tambahEvent($events, $p, $periodeJam);
                        }
                    }
                }
            }
        }

        return view('livewire.kalenderperjam', [
            'events' => $events
        ]);
    }

    // Helper function untuk menambahkan event berdasarkan format tanggal dan jam
    private function tambahEvent(&$events, $penyewaan, $data)
    {
        try {
            // Gabungkan tanggal dan jam menjadi format datetime
            $tanggal = $data['tgl_mulai'];
            $jamMulai = $data['jam_mulai'];
            $jamSelesai = $data['jam_selesai'];

            $startDateTime = Carbon::parse($tanggal . ' ' . $jamMulai);
            $endDateTime = Carbon::parse($tanggal . ' ' . $jamSelesai);

            $events[] = [
                'title' => 'Tidak Tersedia',
                'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                'textColor' => '#000000',
                'backgroundColor' => '#ffffff',
                'borderColor' => 'transparent',
                'extendedProps' => [
                    'tempat' => $penyewaan->lokasi->tempat->nama,
                    'lokasi' => $penyewaan->lokasi->nama_lokasi
                ]
            ];
        } catch (\Exception $e) {
            // Handle kesalahan parsing tanggal
            // Uncomment untuk debugging
            // dd($e->getMessage(), $data);
        }
    }
}
