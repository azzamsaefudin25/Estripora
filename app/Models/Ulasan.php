<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ulasan extends Model
{
    use HasFactory;

    protected $table = 'ulasan';
    protected $primaryKey = 'id_ulasan';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['id_penyewaan', 'nik', 'ulasan', 'rating', 'like', 'dislike'];

    public function penyewaan()
    {
        return $this->belongsTo(Penyewaan::class, 'id_penyewaan');
    }

    public function user()
    {
        return $this->belongsTo(Penyewaan::class, 'nik');
    }
    
    public function reaksi()
    {
        return $this->hasMany(ReaksiUlasan::class, 'id_ulasan', 'id_ulasan');
    }

    // Helper method to get a user's reaction to this review
    public function userReaction($userId)
    {
        return $this->reaksi()->where('id_user', $userId)->first();
    }

    // Helper method to check if user has liked this review
    public function isLikedByUser($userId)
    {
        $reaksi = $this->userReaction($userId);
        return $reaksi && $reaksi->tipe_reaksi === 'like';
    }

    // Helper method to check if user has disliked this review
    public function isDislikedByUser($userId)
    {
        $reaksi = $this->userReaction($userId);
        return $reaksi && $reaksi->tipe_reaksi === 'dislike';
    }
}
