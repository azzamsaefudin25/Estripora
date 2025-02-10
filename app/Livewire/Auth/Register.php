<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class Register extends Component
{
    public $nik, $name, $username, $email, $phone, $otp, $password, $captcha;

    public function save()
    {
        $this->validate([
            'nik' => 'required|unique:users|max:16',
            'name' => 'required|max:255',
            'username' => 'required|unique:users|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|unique:users|max:13',
            'password' => 'required|min:8|max:255',

        ]);

        $user = User::create([
            'nik' => $this->nik,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'password' => Hash::make($this->password),
        ]);

        return redirect()->route('login')->with('success', 'Registrasi sukses! Silakan login.');
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
