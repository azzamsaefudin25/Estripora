<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Header extends Component
{
    public $showMobileMenu = false;

    protected $listeners = ['closeMobileMenus' => 'closeMobileMenu'];

    public function toggleMobileMenu()
    {
        $this->showMobileMenu = !$this->showMobileMenu;

        // agar saat header dibuka otomatis menutup sidebar
        if ($this->showMobileMenu) {
            $this->dispatch('closeSidebar');
        }
    }

    public function closeMobileMenu()
    {
        $this->showMobileMenu = false;
    }

    public function render()
    {
        return view('livewire.partials.header');
    }
}
