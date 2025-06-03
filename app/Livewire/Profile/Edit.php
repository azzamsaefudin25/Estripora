<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Edit extends Component
{
    public $name, $email, $username, $nik, $phone, $user;

    public function mount()
    {
        $this->user = Auth::user();
        $this->name =  $this->user->name;
        $this->email =  $this->user->email;
        $this->username =  $this->user->username;
        $this->nik =  $this->user->nik;
        $this->phone =  $this->user->phone;
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|max:255',
            'username' => 'required|unique:users|max:255',
            'email' => 'required|email|unique:users|max:255',
            'phone' => 'required|unique:users|max:13',
        ], [
            'name.required' => 'Nama wajib diisi',
            'username.required' => 'Username wajib diisi',
            'username.unique' => 'Username sudah di terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah di terdaftar',
            'phone.required' => 'Phone wajib diisi',
            'phone.unique' => 'Phone sudah di terdaftar',
        ]);

        $this->user->update([
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
        ]);


        return redirect()->route('indexProfile')->with('success', "Profile berhasil diupdate");
    }

    public function render()
    {
        return view('livewire.profile.edit');
    }
}
