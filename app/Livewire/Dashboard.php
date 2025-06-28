<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tempat;
use App\Models\Berita;

class Dashboard extends Component
{
    public $query = '';
    public $kategori = '';
    public $categories = [];
    public $filteredTempat = [];
    public $beritaIndex = 0;
    public $beritaList = [];

    public function mount()
    {
        // ambil semua berita terbaru
        $this->beritaList = Berita::orderByDesc('created_at')
            ->get()
            ->map(fn($b) => [
                'img'  => asset('storage/' . $b->img),
                'text' => $b->text,
                'id'   => $b->id,
            ])->toArray();

        // Ambil kategori unik dari database
        $this->categories = Tempat::distinct()->pluck('kategori')->toArray();
        $this->performSearch();
    }

    public function performSearch()
    {
        $query = Tempat::query();

        if (!empty($this->query)) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->query . '%')
                    ->orWhere('rentang_harga', 'like', '%' . $this->query . '%');
            });
        }

        // Filter berdasarkan kategori
        if (!empty($this->kategori)) {
            $query->where('kategori', $this->kategori);
        }

        // Ambil hasil pencarian
        $tempats = $query->get();

        // Kelompokkan hasil pencarian berdasarkan kategori
        $this->filteredTempat = $tempats->isEmpty() ? [] : $tempats->groupBy('kategori')->toArray();

        // mengirimkan event ke blade
        $this->dispatch('search-completed', [
            'filteredTempat' => $this->filteredTempat,
            'query' => $this->query
        ]);
    }

    public function updated($property)
    {
        // Secara otomatis melakukan pencarian ketika properti diperbarui
        if (in_array($property, ['query', 'kategori'])) {
            $this->performSearch();
        }
    }

    public function search()
    {
        $this->performSearch();
    }

    public function nextBerita()
    {
        if (count($this->beritaList) === 0) return;
        $this->beritaIndex = ($this->beritaIndex + 1) % count($this->beritaList);
        $this->dispatch('berita-updated');
    }

    public function prevBerita()
    {
        if (count($this->beritaList) === 0) return;
        $this->beritaIndex = ($this->beritaIndex - 1 + count($this->beritaList)) % count($this->beritaList);
        $this->dispatch('berita-updated');
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
