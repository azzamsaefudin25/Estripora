<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;

    protected $table = 'lokasi';
    protected $primaryKey = 'id_lokasi';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['id_tempat', 'nama_lokasi', 'tarif'];
    public function setAttribute($key, $value)
    {
        if (is_string($value)) {
            $value = strip_tags(trim($value));
        }

        return parent::setAttribute($key, $value);
    }

    public function tempat()
    {
        return $this->belongsTo(Tempat::class, 'id_tempat', 'id');
    }

    public function penyewaan()
    {
        return $this->hasMany(Penyewaan::class, 'id_lokasi', 'id_lokasi');
    }
}
