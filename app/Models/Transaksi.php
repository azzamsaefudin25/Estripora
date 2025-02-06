<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;

    protected $table='transaksi';
    protected $fillable = ['id','id_billing','nik','uraian','tgl_booking','jumlah','luas','tarif','subtotal','metode_pembayaran','status' ];

    public function customer()
    {
        return $this->belongsTo(Tempat::class, 'nik');
    }
}
