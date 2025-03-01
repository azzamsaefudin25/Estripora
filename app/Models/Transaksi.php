<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['id_penyewaan', 'id_billing', 'nik', 'tgl_booking', 'detail_penyewaan', 'total_durasi', 'luas', 'tarif', 'sub_total', 'metode_pembayaran', 'status'];

    protected $casts = [
        'detail_penyewaan' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($transaksi) {
            Penyewaan::where('id_penyewaan', $transaksi->id_penyewaan)
                ->update(['status' => 'Confirmed']);
        });
    }


    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan');
    }
}
