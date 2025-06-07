<?php

namespace App\Livewire\Partials;

use Livewire\Component;

class Sidebar extends Component
{
    public $showSidebar = false;

    protected $listeners = [
        'toggleSidebar' => 'toggleSidebar',
        'closeSidebar' => 'closeSidebar'
    ];

    public function toggleSidebar()
    {
        $this->showSidebar = !$this->showSidebar;

         // agar saat sidebar dibuka otomatis menutup header
        if ($this->showSidebar) {
            $this->dispatch('closeMobileMenus');
        }
    }

    public function closeSidebar()
    {
        $this->showSidebar = false;
    }

    public function render()
    {
        return view('livewire.partials.sidebar');
    }
}
