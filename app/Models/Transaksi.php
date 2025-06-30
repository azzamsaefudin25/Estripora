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
        'metode_pembayaran',
        'status',
        'bukti_bayar',
        'expired_at',
        'reviewed_at',
        'checkout_session'
    ];

    protected $casts = [
        'tgl_booking' => 'date',
        'detail_penyewaan' => 'array',
        'expired_at' => 'datetime',
        'reviewed_at' => 'datetime'
    ];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan', 'id_penyewaan');
    }

    public function user()
    {
    return $this->belongsTo(User::class, 'id_user');
    }
}
