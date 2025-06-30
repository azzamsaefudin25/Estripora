<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tempat extends Model
{
    use HasFactory;

    protected $table = 'tempat';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nama', 'kategori', 'image', 'image2', 'image3', 'image4', 'image5', 'deskripsi', 'kategori_sewa', 'rentang_harga'];

    public function lokasi()
    {
        return $this->hasMany(Lokasi::class, 'id_tempat', 'id');
    }

    // Mendapatkan semua penyewaan melalui lokasi
    public function penyewaans()
    {
        return $this->hasManyThrough(
            Penyewaan::class,
            Lokasi::class,
            'id_tempat',
            'id_lokasi',
            'id',
            'id_lokasi'
        );
    }

    // Mendapatkan semua ulasan melalui penyewaan dan lokasi
    public function ulasans()
    {
        return $this->hasManyDeep(
            Ulasan::class,
            [Lokasi::class, Penyewaan::class],
            [
                'id_tempat',
                'id_lokasi',
                'id_penyewaan'
            ],
            [
                'id',
                'id_lokasi',
                'id_penyewaan'
            ]
        );
    }
    public function setAttribute($key, $value)
    {
        if (is_string($value)) {
            $value = strip_tags(trim($value));
        }

        return parent::setAttribute($key, $value);
    }

    // Hitung rating rata-rata
    public function getRatingRataRataAttribute()
    {
        $ulasans = collect();
        foreach ($this->lokasi as $lokasi) {
            foreach ($lokasi->penyewaan as $penyewaan) {
                if ($penyewaan->ulasan) {
                    $ulasans->push($penyewaan->ulasan);
                }
            }
        }

        if ($ulasans->count() > 0) {
            return round($ulasans->avg('rating'), 1);
        }

        return 0;
    }

    // Hitung jumlah ulasan
    public function getJumlahUlasanAttribute()
    {
        $count = 0;
        foreach ($this->lokasi as $lokasi) {
            foreach ($lokasi->penyewaan as $penyewaan) {
                if ($penyewaan->ulasan) {
                    $count++;
                }
            }
        }

        return $count;
    }
}
