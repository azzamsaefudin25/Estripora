<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penyewaan extends Model
{
    use HasFactory;

    protected $table='penyewaan';
    protected $fillable = ['id_penyewaan','nik','id_tempat','tgl_booking','tgl_mulai','tgl_selesai','jumlah','tarif','status' ];

    public function customer()
    {
        return $this->belongsTo(Tempat::class, 'nik');
    }

    public function tempat()
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
