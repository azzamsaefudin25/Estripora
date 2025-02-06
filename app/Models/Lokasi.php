<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';

    protected $fillable = ['id_lokasi', 'id_tempat', 'nama_lokasi', 'tarif'];

    public function tempat()
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}

