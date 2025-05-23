<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tempat;

class Tempats extends Component
{
    public $tempats;
    public $groupedTempats = [];
    public $query = "";
    public $noResults = false;

    protected $listeners = [
        'search-completed' => 'updateResults'
    ];

    public function mount()
    {
        // Fetch all tempat records with their related lokasi and penyewaan
        $this->tempats = Tempat::with(['lokasi.penyewaan.ulasan'])->get();

        // Tambahkan data rating ke setiap tempat
        $this->tempats = $this->tempats->map(function ($tempat) {
            $tempat['rating_rata_rata'] = $tempat->getRatingRataRataAttribute();
            $tempat['jumlah_ulasan'] = $tempat->getJumlahUlasanAttribute();
            return $tempat;
        });

        // Group by kategori
        $this->groupedTempats = $this->tempats->groupBy('kategori')->toArray();
    }

    public function updateResults($data)
    {
        // Mendapatkan data tempat yang sudah difilter
        $tempatsFiltered = collect($data['filteredTempat'])->map(function ($items, $kategori) {
            return collect($items)->map(function ($item) {
                $tempat = Tempat::with(['lokasi.penyewaan.ulasan'])->find($item['id']);
                if ($tempat) {
                    $item['rating_rata_rata'] = $tempat->getRatingRataRataAttribute();
                    $item['jumlah_ulasan'] = $tempat->getJumlahUlasanAttribute();
                } else {
                    $item['rating_rata_rata'] = 0;
                    $item['jumlah_ulasan'] = 0;
                }
                return $item;
            })->toArray();
        })->toArray();

        $this->groupedTempats = $tempatsFiltered;
        $this->query = $data['query'];

        $this->noResults = (empty($data['filteredTempat']) && !empty($data['query']));
    }

    public function render()
    {
        return view('livewire.tempats', [
            'groupedTempats' => $this->groupedTempats,
            'query' => $this->query,
            'noResults' => $this->noResults
        ]);
    }
}
