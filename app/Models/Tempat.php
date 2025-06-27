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
            'id_tempat', // Foreign key di tabel lokasi yang mengacu ke tempat
            'id_lokasi', // Foreign key di tabel penyewaan yang mengacu ke lokasi
            'id', // Local key di tabel tempat
            'id_lokasi' // Local key di tabel lokasi
        );
    }

    // Mendapatkan semua ulasan melalui penyewaan dan lokasi
    public function ulasans()
    {
        return $this->hasManyDeep(
            Ulasan::class,
            [Lokasi::class, Penyewaan::class],
            [
                'id_tempat', // Foreign key di lokasi yang mengacu ke tempat
                'id_lokasi', // Foreign key di penyewaan yang mengacu ke lokasi
                'id_penyewaan' // Foreign key di ulasan yang mengacu ke penyewaan
            ],
            [
                'id', // Local key di tempat
                'id_lokasi', // Local key di lokasi
                'id_penyewaan' // Local key di penyewaan
            ]
        );
    }

    // Hitung rating rata-rata
    public function getRatingRataRataAttribute()
    {
        // Kita perlu mendapatkan ulasan melalui relasi penyewaan -> lokasi
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
