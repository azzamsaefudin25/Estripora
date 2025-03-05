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
        // Fetch all tempat records initially
        $this->tempats = Tempat::all();
        
        // Group by kategori
        $this->groupedTempats = $this->tempats->groupBy('kategori')->toArray();
    }
    
    public function updateResults($data)
    {
        $this->groupedTempats = $data['filteredTempat'];
        $this->query = $data['query'];
        
        // Explicitly set noResults flag based on the search results
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