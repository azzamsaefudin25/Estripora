<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;

    protected $table='transaksi';

    protected $primaryKey = 'id'; 
    public $incrementing = true; 
    protected $keyType = 'int'; 

    protected $fillable = ['id_penyewaan','id_billing','nik','uraian','tgl_booking','jumlah','luas','tarif','sub_total','metode_pembayaran','status' ];

    public function user()
    {
        return $this->belongsTo(User::class, 'nik');
    }
}
