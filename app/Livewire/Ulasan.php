<?php

namespace App\Livewire;

use App\Models\Ulasan as UlasanModel;
use App\Models\ReaksiUlasan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Ulasan extends Component
{
    use WithPagination;

    // protected $paginationTheme = 'tailwind';

    // Filter property
    public $ratingFilter = 'all'; // Options: 'all', 'highest', 'lowest'
    public $tempatId = null; // Property to filter by tempat

    // For modal
    public $showLoginModal = false;

    public function mount($tempatId = null)
    {
        // Initialize with 'all' filter
        $this->ratingFilter = 'all';
        $this->tempatId = $tempatId;
    }

    public function setFilter($filter)
    {
        $this->ratingFilter = $filter;
        // Reset pagination when filter changes
        $this->resetPage();
    }

    public function toggleReaksi($ulasanId, $tipeReaksi)
    {
        // Check if user is logged in
        if (!Auth::check()) {
            $this->showLoginModal = true;
            return;
        }

        $userId = Auth::id();
        $ulasan = UlasanModel::findOrFail($ulasanId);

        // Check if the user already has a reaction to this review
        $existingReaksi = ReaksiUlasan::where('id_ulasan', $ulasanId)
            ->where('id_user', $userId)
            ->first();

        if ($existingReaksi) {
            // If same reaction type, remove it (unlike/undislike)
            if ($existingReaksi->tipe_reaksi === $tipeReaksi) {
                // Decrement the counter based on the reaction type
                if ($tipeReaksi === 'like') {
                    $ulasan->decrement('like');
                } else {
                    $ulasan->decrement('dislike');
                }

                // Delete the reaction
                $existingReaksi->delete();
            } else {
                // If different reaction type, update it and adjust counters
                if ($tipeReaksi === 'like') {
                    $ulasan->increment('like');
                    $ulasan->decrement('dislike');
                } else {
                    $ulasan->increment('dislike');
                    $ulasan->decrement('like');
                }

                // Update the reaction type
                $existingReaksi->update(['tipe_reaksi' => $tipeReaksi]);
            }
        } else {
            // Create new reaction and increment counter
            ReaksiUlasan::create([
                'id_ulasan' => $ulasanId,
                'id_user' => $userId,
                'tipe_reaksi' => $tipeReaksi
            ]);

            if ($tipeReaksi === 'like') {
                $ulasan->increment('like');
            } else {
                $ulasan->increment('dislike');
            }
        }
    }

    public function likeUlasan($ulasanId)
    {
        $this->toggleReaksi($ulasanId, 'like');
    }

    public function dislikeUlasan($ulasanId)
    {
        $this->toggleReaksi($ulasanId, 'dislike');
    }

    public function closeLoginModal()
    {
        $this->showLoginModal = false;
    }

    public function redirectToLogin()
    {
        return redirect()->route('login');
    }

    public function render()
    {
        // Get the current location ID from the URL if available
        $id_lokasi = request()->route('id_lokasi');

        $query = UlasanModel::with(['penyewaan.lokasi.tempat', 'penyewaan.user']);

        // Apply rating filters
        switch ($this->ratingFilter) {
            case 'highest':
                $query->orderBy('rating', 'desc');
                break;
            case 'lowest':
                $query->orderBy('rating', 'asc');
                break;
            default:
                // Default sorting by date for 'all' filter
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Filter by location if ID is provided
        if ($id_lokasi) {
            $query->whereHas('penyewaan.lokasi', function ($q) use ($id_lokasi) {
                $q->where('id_lokasi', $id_lokasi);
            });
        }

        // Filter by tempat ID if provided
        if ($this->tempatId) {
            $query->whereHas('penyewaan.lokasi.tempat', function ($q) {
                $q->where('id_tempat', $this->tempatId);
            });
        }

        $ulasans = $query->paginate(10);

        // Get current user's reactions for all fetched reviews
        $userReactions = [];
        if (Auth::check()) {
            $userId = Auth::id();
            foreach ($ulasans as $ulasan) {
                $reaction = ReaksiUlasan::where('id_ulasan', $ulasan->id_ulasan)
                    ->where('id_user', $userId)
                    ->first();

                if ($reaction) {
                    $userReactions[$ulasan->id_ulasan] = $reaction->tipe_reaksi;
                }
            }
        }

        return view('livewire.ulasan', [
            'ulasans' => $ulasans,
            'userReactions' => $userReactions
        ]);
    }
}
