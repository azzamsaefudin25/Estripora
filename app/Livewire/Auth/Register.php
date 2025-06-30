<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class Register extends Component
{
    public $nik, $name, $username, $email, $phone, $otp, $password, $captcha;

    public function register()
    {
        $this->validate([
            'nik' => 'required|unique:users|max:16',
            'name' => 'required|max:255',
            'username' => 'required|unique:users|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|unique:users|max:13',
            'password' => 'required|min:6|max:255',

        ],[
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
        ]);

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
