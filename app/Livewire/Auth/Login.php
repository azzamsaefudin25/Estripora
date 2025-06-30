<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Support\Facades\Session;

class Login extends Component
{
    public $email;
    public $password;
    public $captcha;
    public $captcha_code;

    public function mount()
    {
        $this->generateCaptcha();
    }

    public function generateCaptcha()
    {
        // Generate random captcha code (6 characters: letters and numbers)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $this->captcha_code = '';
        for ($i = 0; $i < 6; $i++) {
            $this->captcha_code .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Store captcha in session
        session(['captcha_code' => $this->captcha_code]);
    }

    public function refreshCaptcha()
    {
        $this->generateCaptcha();
        $this->captcha = '';
    }

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
            'captcha' => 'required',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'captcha.required' => 'Captcha wajib diisi',
        ]);

        // Verify captcha
        if (strtolower($this->captcha) !== strtolower(session('captcha_code'))) {
            $this->addError('captcha', 'Captcha tidak valid');
            $this->refreshCaptcha();
            return;
        }

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            session()->forget('captcha_code'); // Clear captcha from session
            $userName = Auth::user()->name;
            return redirect()->route('dashboard')->with('success', "Selamat datang di Estripora, {$userName}!");
        }

        $this->addError('login', 'Email atau password salah');
        $this->refreshCaptcha(); // Refresh captcha on failed login
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
