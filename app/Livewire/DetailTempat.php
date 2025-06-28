<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Tempat;
use App\Models\Lokasi;

class DetailTempat extends Component
{
    public $tempatId;
    public $tempat;
    public $lokasi = [];
    public $selectedLokasi = null;

    public function mount($id)
    {
        $this->tempatId = $id;
        $this->tempat = $this->getTempatDetail();
        $this->getLokasi();
    }

    public function render()
    {
        return view('livewire.detail-tempat');
    }

    private function getTempatDetail()
    {
        $tempat = Tempat::find($this->tempatId);

        if ($tempat) {
            return $tempat;
        }

        return (object) [
            'id' => $this->tempatId,
            'nama' => 'Tempat Tidak Ditemukan',
            'deskripsi' => 'Deskripsi tidak tersedia.',
            'image' => 'images/default.jpg',
            'image2' => 'images/default.jpg',
            'image3' => 'images/default.jpg',
            'image4' => 'images/default.jpg',
            'image5' => 'images/default.jpg'
        ];
    }

    private function getLokasi()
    {
        // Ambil semua lokasi berdasarkan id_tempat
        if ($this->tempat && isset($this->tempat->id)) {
            $this->lokasi = Lokasi::where('id_tempat', $this->tempat->id)->get();
        }
    }

    public function selectedLokasi($value)
    {
        $this->selectedLokasi = $value;
    }
}
