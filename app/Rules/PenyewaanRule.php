<?php

namespace App\Rules;

use Carbon\Carbon;
use App\Models\Penyewaan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        $query = Penyewaan::where('id_lokasi', $this->idLokasi);

        if ($this->currentId) {
            $query->where('id_penyewaan', '!=', $this->currentId);
        }

        $this->existingRentals = $query->get();

        // Debuglog
        Log::info('Loading existing rentals', [
            'id_lokasi' => $this->idLokasi,
            'current_id' => $this->currentId,
            'count' => $this->existingRentals->count(),
            'rentals' => $this->existingRentals->toArray()
        ]);
    }

    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        try {
            // Debuglog input value
            Log::info('Validating new rental', [
                'attribute' => $attribute,
                'value' => $value,
                'kategori_sewa' => $this->kategoriSewa
            ]);

            $newTimeRanges = $this->getTimeRanges($value, $this->kategoriSewa);
            $existingTimeRanges = $this->getExistingTimeRanges();

            // Debuglog time ranges
            Log::info('Time ranges', [
                'new' => $newTimeRanges,
                'existing' => $existingTimeRanges
            ]);

            // Jika hanya ada satu rentang waktu, tidak perlu cek internal overlap
            if (count($newTimeRanges) <= 1) {
                $hasOverlap = $this->hasOverlap($newTimeRanges, $existingTimeRanges);

                // Debuglog overlap check result
                Log::info('Single time range overlap check', [
                    'has_overlap' => $hasOverlap
                ]);

                return !$hasOverlap;
            }

            // Cek overlap antara rentang waktu baru
            if (!$this->validateInternalOverlap($newTimeRanges)) {
                Log::info('Internal overlap detected');
                return false;
            }

            $hasOverlap = $this->hasOverlap($newTimeRanges, $existingTimeRanges);
            Log::info('Multiple time ranges overlap check', [
                'has_overlap' => $hasOverlap
            ]);

            return !$hasOverlap;
        } catch (\Exception $e) {
            Log::error('PenyewaanRule validation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    protected function getTimeRanges($rentals, $type): array
    {
        $ranges = [];
        $rentals = is_array($rentals) ? $rentals : [$rentals];

        foreach ($rentals as $rental) {
            try {
                if ($type === 'per jam') {
                    if (empty($rental['tgl_mulai']) || empty($rental['jam_mulai']) || empty($rental['jam_selesai'])) {
                        continue;
                    }

                    $start = Carbon::parse($rental['tgl_mulai'] . ' ' . $rental['jam_mulai']);
                    $end = Carbon::parse($rental['tgl_mulai'] . ' ' . $rental['jam_selesai']);

                    // Handle kasus melewati tengah malam
                    if ($end->lt($start)) {
                        $end->addDay();
                    }

                    // Debuglog time parsing
                    Log::info('Parsed time range', [
                        'original' => $rental,
                        'start' => $start->toDateTimeString(),
                        'end' => $end->toDateTimeString()
                    ]);
                } else { // per hari
                    if (empty($rental['tgl_mulai']) || empty($rental['tgl_selesai'])) {
                        continue;
                    }

                    $start = Carbon::parse($rental['tgl_mulai'])->startOfDay();
                    $end = Carbon::parse($rental['tgl_selesai'])->endOfDay();
                }

                $ranges[] = [
                    'start' => $start->timestamp,
                    'end' => $end->timestamp
                ];
            } catch (\Exception $e) {
                Log::error('Error parsing rental time', [
                    'rental' => $rental,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        return $ranges;
    }

    protected function getExistingTimeRanges(): array
    {
        $ranges = [];
        foreach ($this->existingRentals as $rental) {
            try {
                if (!empty($rental->penyewaan_per_jam)) {
                    $hourlyRentals = is_string($rental->penyewaan_per_jam)
                        ? json_decode($rental->penyewaan_per_jam, true)
                        : $rental->penyewaan_per_jam;

                    if (is_array($hourlyRentals)) {
                        $ranges = array_merge($ranges, $this->getTimeRanges($hourlyRentals, 'per jam'));
                    }
                }

                if (!empty($rental->penyewaan_per_hari)) {
                    $dailyRentals = is_string($rental->penyewaan_per_hari)
                        ? json_decode($rental->penyewaan_per_hari, true)
                        : $rental->penyewaan_per_hari;

                    if (is_array($dailyRentals)) {
                        $ranges = array_merge($ranges, $this->getTimeRanges($dailyRentals, 'per hari'));
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error processing existing rental', [
                    'rental_id' => $rental->id_penyewaan,
                    'error' => $e->getMessage()
                ]);
                continue;
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
                    // Debuglog when overlap is found
                    Log::info('Overlap detected', [
                        'new_range' => [
                            'start' => date('Y-m-d H:i:s', $new['start']),
                            'end' => date('Y-m-d H:i:s', $new['end'])
                        ],
                        'existing_range' => [
                            'start' => date('Y-m-d H:i:s', $existing['start']),
                            'end' => date('Y-m-d H:i:s', $existing['end'])
                        ]
                    ]);
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
