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
    public $foto, $foto2, $foto3; // File baru
    public $fotoLama1, $fotoLama2, $fotoLama3; // Path lama (jika edit)
    public $isEditing = false;
    public $showEditModal = false;



    protected $rules = [
        'email' => 'required|email|exists:users,email',
        'id_penyewaan' => 'required|string',
        'keluhan' => 'required|string',
        'foto' => 'nullable|image|max:2048',
        'foto2' => 'nullable|image|max:2048',
        'foto3' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        if (!Auth::check()) {
            session()->flash('error', 'Anda harus login terlebih dahulu.');
            return redirect()->route('dashboard');
        }

        $this->email = auth()->user()->email;
    }

    public function removeFoto($field)
    {
        if (in_array($field, ['foto', 'foto2', 'foto3'])) {
            $this->$field = null;
        }
    }
    public function submit()
    {
        $this->validate();

        $foto1Path = $this->foto ? $this->foto->store('lapor_foto', 'public') : null;
        $foto2Path = $this->foto2 ? $this->foto2->store('lapor_foto', 'public') : null;
        $foto3Path = $this->foto3 ? $this->foto3->store('lapor_foto', 'public') : null;

        Lapors::create([
                'email' => $this->email,
                'id_penyewaan' => $this->id_penyewaan,
                'keluhan' => $this->keluhan,
                'foto' => $foto1Path,
                'foto2' => $foto2Path,
                'foto3' => $foto3Path,
            ]);
        session()->flash('message', 'Laporan berhasil dikirim.');

        $this->resetForm();
    }

   
    public function removeFotoLama($slot)
    {
        if ($slot === 1) $this->fotoLama1 = null;
        elseif ($slot === 2) $this->fotoLama2 = null;
        elseif ($slot === 3) $this->fotoLama3 = null;
    }

    public function render()
    {
        $laporanSebelumnya = Lapors::where('email', $this->email)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.lapor', [
            'laporanSebelumnya' => $laporanSebelumnya,
            'isEditing' => $this->isEditing,
        ]);
    }

    public function editLaporan($id)
    {
        $lapor = Lapors::findOrFail($id);

        $this->email = $lapor->email;
        $this->id_penyewaan = $lapor->id_penyewaan;
        $this->keluhan = $lapor->keluhan;
        $this->fotoLama1 = $lapor->foto;
        $this->fotoLama2 = $lapor->foto2;
        $this->fotoLama3 = $lapor->foto3;

        $this->isEditing = true;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->batalEdit(); // reset form juga
    }



    public function batalEdit()
    {
        $this->resetForm();
    }


    private function resetForm()
    {
        $this->reset([
            'id_penyewaan', 'keluhan',
            'foto', 'foto2', 'foto3',
            'fotoLama1', 'fotoLama2', 'fotoLama3', 'isEditing'
        ]);
    }


}