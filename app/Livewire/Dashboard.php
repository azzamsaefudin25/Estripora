<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public $query = '';
    public $kategori = 'Semua Kategori';
    public $categories = ['Semua Kategori', 'A', 'B', 'C'];
    public $beritaIndex = 0;
    public $beritaList = [
        ['img' => '/images/qrisready.png', 'text' => 'Berita pertama: QRIS Ready!'],
        ['img' => '/images/estriporalogo.png', 'text' => 'Berita kedua: Update fitur terbaru.'],
        ['img' => '/images/estriporalogo.png', 'text' => 'Berita ketiga: Diskon spesial bulan ini!'],
    ];

    public function search()
    {
        // Logika pencarian bisa ditambahkan di sini
        $this->dispatch('searchTriggered', $this->query, $this->kategori);
    }

    public function nextBerita()
    {
        $this->beritaIndex = ($this->beritaIndex + 1) % count($this->beritaList);
        $this->dispatch('berita-updated'); // Memicu Alpine.js
    }
    
    public function prevBerita()
    {
        $this->beritaIndex = ($this->beritaIndex - 1 + count($this->beritaList)) % count($this->beritaList);
        $this->dispatch('berita-updated'); // Memicu Alpine.js
    }
    

    public function render()
    {
        return view('livewire.dashboard');
    }
}
