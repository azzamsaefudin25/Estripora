<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tempat;

class DetailTempat extends Component
{
    public $tempatId;
    public $tempat;

    public function mount($id)
    {
        $this->tempatId = $id;
        $this->tempat = $this->getTempatDetail();
    }

    public function render()
    {
        return view('livewire.detail-tempat');
    }

    private function getTempatDetail()
    {
        // Gunakan data dari database jika tersedia
        $tempat = Tempat::find($this->tempatId);
        
        if ($tempat) {
            return $tempat; // Jika ada di database, kembalikan sebagai objek
        }

        // Jika tidak ada di database, gunakan data dummy sebagai objek
        return (object) [
            'id' => $this->tempatId,
            'nama' => 'Gedung Manunggal Jati',
            'deskripsi' => 'Deskripsi detail tempat...',
            'img' => 'path/to/gedung.png'
        ];
    }
}
