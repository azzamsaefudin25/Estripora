<?php

namespace App\Rules;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use App\Models\Penyewaan;

class JadwalPenyewaanRule implements Rule
{
    protected $message;
    protected $locationId;
    protected $penyewaanId;
    protected $type;

    public function __construct($locationId, $type, $penyewaanId = null)
    {
        $this->locationId = $locationId;
        $this->type = $type; // 'per_jam' or 'per_hari'
        $this->penyewaanId = $penyewaanId;
    }

    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true;
        }

        // Get existing penyewaan for the location
        $query = Penyewaan::where('id_lokasi', $this->locationId)
            ->where('status', '!=', 'Cancelled');

        if ($this->penyewaanId) {
            $query->where('id', '!=', $this->penyewaanId);
        }

        $existingPenyewaan = $query->get();

        // Check for conflicts with existing bookings
        foreach ($value as $schedule) {
            if ($this->type === 'per_jam') {
                if (!$this->validateHourlySchedule($schedule, $existingPenyewaan)) {
                    return false;
                }
            } else {
                if (!$this->validateDailySchedule($schedule, $existingPenyewaan)) {
                    return false;
                }
            }
        }

        // Check for internal conflicts
        if (!$this->validateInternalConflicts($value)) {
            return false;
        }

        return true;
    }

    protected function validateHourlySchedule($schedule, $existingPenyewaan)
    {
        if (!isset($schedule['tgl_mulai'], $schedule['jam_mulai'], $schedule['jam_selesai'])) {
            return true;
        }

        $baseDate = Carbon::parse($schedule['tgl_mulai'])->format('Y-m-d');
        $newStart = Carbon::parse($baseDate . ' ' . $schedule['jam_mulai']);
        $newEnd = Carbon::parse($baseDate . ' ' . $schedule['jam_selesai']);

        if ($newEnd->lt($newStart)) {
            $newEnd->addDay();
        }

        foreach ($existingPenyewaan as $penyewaan) {
            $existingSchedules = is_string($penyewaan->jadwal_per_jam) 
                ? json_decode($penyewaan->jadwal_per_jam, true) 
                : $penyewaan->jadwal_per_jam;

            if (!is_array($existingSchedules)) {
                continue;
            }

            foreach ($existingSchedules as $existingSchedule) {
                $existingDate = Carbon::parse($existingSchedule['tgl_mulai'])->format('Y-m-d');
                if ($baseDate !== $existingDate) {
                    continue;
                }

                $existingStart = Carbon::parse($existingDate . ' ' . $existingSchedule['jam_mulai']);
                $existingEnd = Carbon::parse($existingDate . ' ' . $existingSchedule['jam_selesai']);

                if ($existingEnd->lt($existingStart)) {
                    $existingEnd->addDay();
                }

                if ($this->hasTimeOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                    $this->message = "Jadwal bertabrakan dengan penyewaan yang sudah ada pada tanggal " . 
                        $existingDate . " jam " . $existingSchedule['jam_mulai'] . " - " . $existingSchedule['jam_selesai'];
                    return false;
                }
            }
        }

        return true;
    }

    protected function validateDailySchedule($schedule, $existingPenyewaan)
    {
        if (!isset($schedule['tgl_mulai'], $schedule['tgl_selesai'])) {
            return true;
        }

        $newStart = Carbon::parse($schedule['tgl_mulai']);
        $newEnd = Carbon::parse($schedule['tgl_selesai']);

        foreach ($existingPenyewaan as $penyewaan) {
            $existingSchedules = is_string($penyewaan->jadwal_per_hari) 
                ? json_decode($penyewaan->jadwal_per_hari, true) 
                : $penyewaan->jadwal_per_hari;

            if (!is_array($existingSchedules)) {
                continue;
            }

            foreach ($existingSchedules as $existingSchedule) {
                $existingStart = Carbon::parse($existingSchedule['tgl_mulai']);
                $existingEnd = Carbon::parse($existingSchedule['tgl_selesai']);

                if ($this->hasDateOverlap($newStart, $newEnd, $existingStart, $existingEnd)) {
                    $this->message = "Jadwal bertabrakan dengan penyewaan yang sudah ada pada tanggal " .
                        $existingStart->format('d-m-Y') . " sampai " . $existingEnd->format('d-m-Y');
                    return false;
                }
            }
        }

        return true;
    }

    protected function validateInternalConflicts($schedules)
    {
        $count = count($schedules);
        
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->type === 'per_jam') {
                    if (!isset($schedules[$i]['tgl_mulai'], $schedules[$i]['jam_mulai'], 
                             $schedules[$i]['jam_selesai'], $schedules[$j]['tgl_mulai'],
                             $schedules[$j]['jam_mulai'], $schedules[$j]['jam_selesai'])) {
                        continue;
                    }

                    $baseDate1 = Carbon::parse($schedules[$i]['tgl_mulai'])->format('Y-m-d');
                    $start1 = Carbon::parse($baseDate1 . ' ' . $schedules[$i]['jam_mulai']);
                    $end1 = Carbon::parse($baseDate1 . ' ' . $schedules[$i]['jam_selesai']);
                    
                    $baseDate2 = Carbon::parse($schedules[$j]['tgl_mulai'])->format('Y-m-d');
                    $start2 = Carbon::parse($baseDate2 . ' ' . $schedules[$j]['jam_mulai']);
                    $end2 = Carbon::parse($baseDate2 . ' ' . $schedules[$j]['jam_selesai']);

                    if ($end1->lt($start1)) $end1->addDay();
                    if ($end2->lt($start2)) $end2->addDay();

                    if ($baseDate1 === $baseDate2 && $this->hasTimeOverlap($start1, $end1, $start2, $end2)) {
                        $this->message = "Terdapat jadwal yang bertabrakan dalam pengajuan penyewaan";
                        return false;
                    }
                } else {
                    if (!isset($schedules[$i]['tgl_mulai'], $schedules[$i]['tgl_selesai'],
                             $schedules[$j]['tgl_mulai'], $schedules[$j]['tgl_selesai'])) {
                        continue;
                    }

                    $start1 = Carbon::parse($schedules[$i]['tgl_mulai']);
                    $end1 = Carbon::parse($schedules[$i]['tgl_selesai']);
                    $start2 = Carbon::parse($schedules[$j]['tgl_mulai']);
                    $end2 = Carbon::parse($schedules[$j]['tgl_selesai']);

                    if ($this->hasDateOverlap($start1, $end1, $start2, $end2)) {
                        $this->message = "Terdapat jadwal yang bertabrakan dalam pengajuan penyewaan";
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function hasTimeOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lt($end2) && $start2->lt($end1);
    }

    protected function hasDateOverlap($start1, $end1, $start2, $end2)
    {
        return $start1->lte($end2) && $start2->lte($end1);
    }

    public function message()
    {
        return $this->message ?? 'Jadwal tidak tersedia.';
    }
}