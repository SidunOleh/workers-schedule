<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class LogOutController extends Controller
{
    public function __construct(
        public AuthService $authService
    )
    {

    }

    public function __invoke()
    {
        $this->authService->logout();

        return response('OK');
    }
}
