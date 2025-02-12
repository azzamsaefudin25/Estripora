<?php

namespace App\Livewire;

use App\Models\Lapors;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Lapor extends Component
{
    use WithFileUploads;

    public $email, $id_penyewaan, $keluhan, $foto;

    protected $rules = [
        'email' => 'required|email|exists:users,email',
        'id_penyewaan' => 'required|string',
        'keluhan' => 'required|string',
        'foto' => 'nullable|image|max:2048', // Maksimal 2MB
    ];
   
    public function mount()
    {
        // Jika pengguna belum login, redirect ke dashboard
        if (!Auth::check()) {
            session()->flash('error', 'Anda harus login terlebih dahulu untuk mengakses halaman Lapor.');
            return redirect()->route('dashboard');
        }
    }

    public function submit()
    {
        $this->validate();

        Lapors::create([
            'email' => $this->email,
            'id_penyewaan' => $this->id_penyewaan,
            'keluhan' => $this->keluhan,
            'foto' => $this->foto ? $this->foto->store('lapor_foto', 'public') : null,
        ]);

        session()->flash('message', 'Laporan berhasil dikirim.');
        
        // Reset form setelah submit
        $this->reset(['email', 'id_penyewaan', 'keluhan', 'foto']);
    }

    public function render()
    {
        return view('livewire.lapor', [
            
        ]);
    }
    
}
