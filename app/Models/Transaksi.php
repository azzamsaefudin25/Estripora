<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'id_penyewaan',
        'id_billing',
        'nik',
        'tgl_booking',
        'detail_penyewaan',
        'total_durasi',
        'tarif',
        'sub_total',
        'status',
    ];

    protected $casts = [
        'tgl_booking' => 'date',
        'detail_penyewaan' => 'array',
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }
    
}
