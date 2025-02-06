<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penyewaan extends Model
{
    use HasFactory;

    protected $table='penyewaan';
    protected $primaryKey = 'id_penyewaan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['nik','id_tempat','tgl_booking','jam_mulai','jam_selesai','tgl_mulai','tgl_selesai','jumlah','tarif','sub_total','status' ];

    public function customer()
    {
        return $this->belongsTo(Tempat::class, 'nik');
    }

    public function tempat()
    {
        return $this->belongsTo(Tempat::class, 'id_tempat');
    }
}
