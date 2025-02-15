<?php

namespace App\Rules;

use Carbon\Carbon;
use App\Models\Penyewaan;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Validation\Rule;

class PenyewaanRule implements Rule
{
    protected $existingRentals;
    protected $kategoriSewa;
    protected $currentId;
    protected $idLokasi;

    public function __construct($idLokasi, $kategoriSewa, $currentId = null)
    {
        $this->idLokasi = $idLokasi;
        $this->kategoriSewa = $kategoriSewa;
        $this->currentId = $currentId;
        $this->loadExistingRentals();
    }

    protected function loadExistingRentals()
    {
        $this->existingRentals = Penyewaan::where('id_lokasi', $this->idLokasi)
            ->when($this->currentId, function ($query) {
                return $query->where('id', '!=', $this->currentId);
            })
            ->get();
    }

    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        // Konversi semua tanggal dan waktu ke timestamps untuk perbandingan
        $newTimeRanges = $this->getTimeRanges($value, $this->kategoriSewa);
        $existingTimeRanges = $this->getExistingTimeRanges();

        // Cek overlap antara rentang waktu baru
        if (!$this->validateInternalOverlap($newTimeRanges)) {
            return false;
        }

        // Cek overlap dengan rentang waktu yang sudah ada
        return !$this->hasOverlap($newTimeRanges, $existingTimeRanges);
    }

    protected function getTimeRanges($rentals, $type): array
    {
        $ranges = [];
        foreach ($rentals as $rental) {
            if ($type === 'per jam') {
                $startDate = Carbon::parse($rental['tgl_mulai']);
                $start = Carbon::parse($rental['tgl_mulai'] . ' ' . $rental['jam_mulai']);
                $end = Carbon::parse($rental['tgl_mulai'] . ' ' . $rental['jam_selesai']);

                // Handle kasus melewati tengah malam
                if ($end->lt($start)) {
                    $end->addDay();
                }
            } else { // per hari
                $start = Carbon::parse($rental['tgl_mulai'])->startOfDay();
                $end = Carbon::parse($rental['tgl_selesai'])->endOfDay();
            }

            $ranges[] = [
                'start' => $start->timestamp,
                'end' => $end->timestamp
            ];
        }
        return $ranges;
    }

    protected function getExistingTimeRanges(): array
    {
        $ranges = [];
        foreach ($this->existingRentals as $rental) {
            // Process per jam rentals
            if (!empty($rental->penyewaan_per_jam)) {
                $hourlyRentals = $rental->penyewaan_per_jam; // Data sudah dalam bentuk array
                if (is_array($hourlyRentals)) {
                    $ranges = array_merge($ranges, $this->getTimeRanges($hourlyRentals, 'per jam'));
                }
            }

            // Process per hari rentals
            if (!empty($rental->penyewaan_per_hari)) {
                $dailyRentals = $rental->penyewaan_per_hari; // Data sudah dalam bentuk array
                if (is_array($dailyRentals)) {
                    $ranges = array_merge($ranges, $this->getTimeRanges($dailyRentals, 'per hari'));
                }
            }
        }
        return $ranges;
    }

    protected function validateInternalOverlap(array $ranges): bool
    {
        $count = count($ranges);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->isOverlapping($ranges[$i], $ranges[$j])) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function hasOverlap(array $newRanges, array $existingRanges): bool
    {
        foreach ($newRanges as $new) {
            foreach ($existingRanges as $existing) {
                if ($this->isOverlapping($new, $existing)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function isOverlapping(array $range1, array $range2): bool
    {
        return $range1['start'] < $range2['end'] && $range2['start'] < $range1['end'];
    }

    public function message(): string
    {
        return 'Terdapat jadwal yang bertabrakan dengan penyewaan yang sudah ada atau jadwal lain yang dipilih.';
    }
}
