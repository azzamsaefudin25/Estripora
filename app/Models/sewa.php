<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sewa extends Model
{
    protected $table = 'sewa';

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class);
    }
}
