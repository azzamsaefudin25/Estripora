<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ulasan extends Model
{
    use HasFactory;

    protected $table='ulasan';
    protected $primaryKey = 'id_ulasan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['id_penyewaan','nik','ulasan','rating' ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan');
    }

    public function user()
    {
        return $this->belongsTo(Penyewaan::class, 'nik');
    }
    
}
