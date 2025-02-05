<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ulasan extends Model
{
    use HasFactory;

    protected $table='ulasan';
    protected $fillable = ['id_ulasan','id_penyewaan','nik','ulasan','rating' ];

    public function penyewaanid()
    {
        return $this->belongsTo(Tempat::class, 'id_penyewaan');
    }

    public function penyewaannik()
    {
        return $this->belongsTo(Tempat::class, 'nik');
    }
    
}
