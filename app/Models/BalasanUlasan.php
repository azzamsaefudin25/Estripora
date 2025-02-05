<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BalasanUlasan extends Model
{
    use HasFactory;

    protected $table='balasanUlasan';

    protected $fillable = ['id', 'id_ulasan', 'nik', 'id_penyewaan','parent_id','ulasan','reaksi'];

    public function penyewaan()
    {
        return $this->belongsTo(Tempat::class, 'id_penyewaan');
    }
    public function ulasan()
    {
        return $this->belongsTo(Tempat::class, 'id_ulasan');
    }
    public function customer()
    {
        return $this->belongsTo(Tempat::class, 'id_nik');
    }
}
