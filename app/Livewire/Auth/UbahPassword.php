<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UbahPassword extends Component
{
    public $current_password;
    public $password;
    public $konfirmasi_password;

    public function ubahPassword()
    {
        // Validasi input
        $this->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6',
            'konfirmasi_password' => 'required|same:password',
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
            'konfirmasi_password.required' => 'Konfirmasi password wajib diisi.',
            'konfirmasi_password.same' => 'Konfirmasi password tidak sesuai.',
        ]);

        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah password saat ini benar
        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }

        /** @var User $user */
        // Update password
        $user->password = Hash::make($this->password);
        $user->save();

        // Reset input
        $this->reset(['current_password', 'password', 'konfirmasi_password']);

        // Kirim notifikasi
        return redirect()->route('ubahpassword')->with('success', "Password berhasil diubah");
    }

    public function render()
    {
        return view('livewire.auth.ubah-password');
    }
}
