<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class transaksi extends Model
{
    protected $table = 'transaksi';

    public function sewa()
    {
        return $this->belongsTo(Sewa::class);
    }

    // Tambahkan accessor agar tarif dan deskripsi bisa diakses langsung
    public function getTarifAttribute()
    {
        return $this->sewa->tarif ?? 0;
    }

    public function getDeskripsiAttribute()
    {
        return $this->sewa->deskripsi ?? 'Tidak ada deskripsi';
    }
}
