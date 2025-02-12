<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lapors extends Model
{
    use HasFactory;

    protected $table='lapor';
    protected $primaryKey = 'id_lapor';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['email', 'id_penyewaan', 'keluhan', 'foto'];



    

    
}
