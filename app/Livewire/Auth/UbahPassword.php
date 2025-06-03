<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UbahPassword extends Component
{
    public $current_password, $password, $konfirmasi_password, $user;

    public function mount()
    {
        $this->user = Auth::user();

        if (!$this->user) {
            return redirect()->route('login');
        }
    }

    public function ubahPassword()
    {
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

        if (!Hash::check($this->current_password, $this->user->password)) {
            $this->addError('current_password', 'Password saat ini tidak sesuai.');
            return;
        }
 
        $this->user->password = Hash::make($this->password);
        $this->user->save();

        $this->reset(['current_password', 'password', 'konfirmasi_password']);

        return redirect()->route('ubahpassword')->with('success', "Password berhasil diubah");
    }

    public function render()
    {
        return view('livewire.auth.ubah-password');
    }
}
