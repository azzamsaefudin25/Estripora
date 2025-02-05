<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tempat extends Model
{
    protected $table='tempat';

    protected $fillable = ['nama', 'kategori', 'image'. 'tarif'];
}
