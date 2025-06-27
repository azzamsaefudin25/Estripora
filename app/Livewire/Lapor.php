<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        'foto' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        'foto2' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        'foto3' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
    ];

    protected $messages = [
        'id_penyewaan.required' => 'Anda harus memilih penyewaan.',
        'keluhan.required'      => 'Kolom keluhan tidak boleh kosong.',
        'keluhan.string'        => 'Masukkan teks keluhan yang valid.',
    ];

   public function mount()
    {
        if (! Auth::check()) {
            // flash error
            session()->flash('error', 'Anda harus login terlebih dahulu untuk mengakses halaman Lapor.');

            // redirect ke login
            return redirect()->route('login');
        }

        $this->email = Auth::user()->email;
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

        // redirect full page dengan flash message
        return redirect()
            ->route('lapor')  
            ->with('message', 'Laporan berhasil dikirim.');
    }

   public function deleteLaporan($id)
    {
        Lapors::findOrFail($id)->delete();
        return redirect()
            ->route('lapor')
            ->with('message', 'Laporan berhasil dihapus.');
    }

    public function viewBalasan($balasan)
    {
        // Jika dia mulai dan diakhiri dengan tanda kutip ganda, pangkas:
        if (Str::startsWith($balasan, '"') && Str::endsWith($balasan, '"')) {
            $balasan = substr($balasan, 1, -1);
        }
        $this->currentBalasan = $balasan;
        $this->showBalasanPanel = true;
    }

    public function closeBalasan()
    {
        $this->showBalasanPanel = false;
    }

    public function removeFoto($field)
    {
        if (in_array($field, ['foto', 'foto2', 'foto3'])) {
            $this->$field = null;
        }
    }

    public function render()
    {
        $laporanSebelumnya = Lapors::where('email', Auth::user()->email)
            ->latest()
            ->get();

        return view('livewire.lapor', compact('laporanSebelumnya'));
    }


}