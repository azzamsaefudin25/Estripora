<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReaksiUlasan extends Model
{
    use HasFactory;

    protected $table = 'reaksi_ulasan';
    
    protected $fillable = [
        'id_ulasan',
        'id_user',
        'tipe_reaksi',
    ];

    public function ulasan()
    {
        return $this->belongsTo(Ulasan::class, 'id_ulasan', 'id_ulasan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}