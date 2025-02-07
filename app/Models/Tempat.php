<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tempat extends Model
{
    use HasFactory;

    protected $table = 'tempat';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nama', 'kategori', 'image', 'deskripsi', 'kategori_sewa', 'rentang_harga'];

    public function lokasi()
    {
        return $this->hasMany(Lokasi::class, 'id_tempat', 'id');
    }
}
