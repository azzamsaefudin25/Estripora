<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tempat extends Model
{
    use HasFactory;

    protected $table='tempat';

    protected $fillable = ['nama', 'kategori', 'image', 'tarif'];
}
