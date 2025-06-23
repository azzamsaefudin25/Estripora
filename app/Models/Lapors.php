<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lapors extends Model
{
    use HasFactory;

    protected $table = 'lapor';
    protected $primaryKey = 'id_lapor';
    public $incrementing = true;
    protected $keyType = 'int';

    // Tambahkan 'email' ke dalam fillable
    protected $fillable = ['email', 'id_penyewaan', 'keluhan', 'foto', 'foto2', 'foto3', 'balasan'];

    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan');
    }
}
