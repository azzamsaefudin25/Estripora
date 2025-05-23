<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penyewaan extends Model
{
    use HasFactory;

    protected $table = 'penyewaan';
    protected $primaryKey = 'id_penyewaan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'id_user',
        'nik',
        'id_lokasi',
        'tgl_booking',
        'kategori_sewa',
        'penyewaan_per_jam',
        'penyewaan_per_hari',
        'total_durasi',
        'tarif',
        'sub_total',
        'status'
    ];

    protected $casts = [
        'penyewaan_per_jam' => 'array',
        'penyewaan_per_hari' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }


    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class, 'id_lokasi');
    }

    public function ulasan()
    {
        return $this->hasOne(Ulasan::class, 'id_penyewaan', 'id_penyewaan');
    }

    public function transaksi()
    {
        return $this->hasOne(Ulasan::class, 'id_penyewaan', 'id_penyewaan');
    }
}
