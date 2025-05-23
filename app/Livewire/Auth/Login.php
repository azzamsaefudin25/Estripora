<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Login extends Component
{
    public $email;
    public $password;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {

            session()->regenerate();
            $userName = Auth::user()->name;
            return redirect()->route('dashboard')->with('success', "Selamat datang di Estripora, {$userName}!");
        }

        return redirect()->route('login')->with('error', 'Email atau password salah.');
    }

    public function logout()
    {
        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();

        return redirect()->route('dashboard')->with('success', "Anda berhasil logout!");
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
