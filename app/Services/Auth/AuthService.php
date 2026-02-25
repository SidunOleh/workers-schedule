<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthService
{
    public function login(array $credentials): User|false
    {
        if (! Auth::attempt($credentials)) {
            return false;
        }

        return Auth::user();
    }

    public function logout()
    {
        Auth::logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}