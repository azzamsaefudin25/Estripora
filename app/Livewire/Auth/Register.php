<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class Register extends Component
{
    public $nik, $name, $username, $email, $phone, $otp, $password, $captcha, $captcha_code;

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


    public function register()
    {
        $this->validate([
            'nik' => 'required|unique:users|max:16',
            'name' => 'required|max:255',
            'username' => 'required|unique:users|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|unique:users|max:13',
            'password' => 'required|min:6|max:255',
            'captcha' => 'required',

        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.unique' => 'NIK sudah di terdaftar',
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah di terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah di terdaftar',
            'phone.required' => 'Phone wajib diisi',
            'phone.unique' => 'Phone sudah di terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal terdiri dari 6 karakter.',
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
        $this->refreshCaptcha();
        $user = User::create([
            'nik' => $this->nik,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);
        Auth::login($user);

        $userName = $user->name;
        return redirect()->route('dashboard')->with('success', "Selamat datang di Estripora, {$userName}!");
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
