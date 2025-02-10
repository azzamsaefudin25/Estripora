<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Header extends Component
{
    // public function logout()
    // {
    //     Auth::logout();
    //     return redirect()->route('login');
    // }

    public function render()
    {
        return view('livewire.partials.header');
    }
}
