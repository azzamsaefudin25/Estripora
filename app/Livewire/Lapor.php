<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use App\Models\Lapors;

class Lapor extends Component
{
    use WithFileUploads;

    public $email, $id_penyewaan, $keluhan;
    public $foto, $foto2, $foto3; 
    public $showBalasanPanel = false;
    public $currentBalasan;



    protected $rules = [
        'email' => 'required|email|exists:users,email',
        'id_penyewaan' => 'required|string',
        'keluhan' => 'required|string',
        'foto' => 'nullable|image|max:2048',
        'foto2' => 'nullable|image|max:2048',
        'foto3' => 'nullable|image|max:2048',
    ];

    public function mount($id = null)
    {
        if (!Auth::check()) {
            session()->flash('error', 'Anda harus login terlebih dahulu.');
            return redirect()->route('dashboard');
        }

        $this->email = auth()->user()->email;

    }


    public function submit()
    {
        $this->validate();
        $paths = [];
        foreach (['foto','foto2','foto3'] as $f) {
            if ($this->$f) {
                $paths[$f] = $this->$f->store('lapor_foto','public');
            }
        }
        Lapors::create(array_merge([
            'email'=>$this->email,
            'id_penyewaan'=>$this->id_penyewaan,
            'keluhan'=>$this->keluhan,
        ], $paths));

        session()->flash('message','Laporan berhasil dikirim.');
        $this->reset(['id_penyewaan','keluhan','foto','foto2','foto3']);
    }

   
   public function deleteLaporan($id)
    {
        Lapors::findOrFail($id)->delete();
        session()->flash('message','Laporan berhasil dihapus.');
    }

    public function viewBalasan($balasan)
    {
        $this->currentBalasan = $balasan;
        $this->showBalasanPanel = true;
    }

    public function closeBalasan()
    {
        $this->showBalasanPanel = false;
    }

    public function render()
    {
        $riwayat = Lapors::where('email', $this->email)
            ->orderByDesc('created_at')->get();
        return view('livewire.lapor', ['laporanSebelumnya' => $riwayat]);
    }


}