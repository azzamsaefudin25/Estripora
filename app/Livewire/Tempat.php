<?php

namespace App\Livewire;

use Livewire\Component;

class Tempat extends Component
{
    public $kategori = "Semua";
    public $query = "";

    public $tempatList = [
        [
            "kategori" => "A",
            "nama" => "Gedung Pertemuan Manunggal Jati",
            "img" => "/images/gedung.png",
            "harga" => "Rp. 2,5jt - 5jt",
        ],
        [
            "kategori" => "A",
            "nama" => "Kantin Manunggal Jati",
            "img" => "/images/gedung.png",
            "harga" => "Rp. 2,5jt - 5jt",
        ],
        [
            "kategori" => "B",
            "nama" => "Gedung Pertemuan Tembalang",
            "img" => "/images/gedung.png",
            "harga" => "Rp. 2,5jt - 5jt",
        ]
    ];

    protected $listeners = ['searchTriggered' => 'updateSearch'];

    public function updateSearch($query, $kategori)
    {
        $this->query = $query;
        $this->kategori = $kategori;
    }
    

    
    public function render()
    {
        $filtered = collect($this->tempatList)
            ->filter(fn($t) => ($this->kategori === "Semua" || $t['kategori'] === $this->kategori) &&
                               (empty($this->query) || str_contains(strtolower($t['nama']), strtolower($this->query))))
            ->sortBy('kategori'); // Mengurutkan berdasarkan kategori sebelum dikelompokkan
    
        // Jika kategori "Semua", pastikan tempat tetap terkelompok
        $filteredTempat = $filtered->isNotEmpty() ? $filtered->groupBy('kategori') : collect([]);
    
        return view('livewire.tempat', compact('filteredTempat'));
    }
    
}
