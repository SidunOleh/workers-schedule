<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LogInRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class LogInController extends Controller
{
    public function __construct(
        public AuthService $authService
    )
    {

    }

    public function __invoke(LogInRequest $request)
    {
        $user = $this->authService->login(
            $request->validated()
        );

        if (! $user) {
            abort(401);
        }

        return $user;
    }
}
