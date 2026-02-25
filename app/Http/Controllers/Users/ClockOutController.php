<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClockOutController extends Controller
{
    public function __construct(
        public UsersService $userService
    )
    {

    }

    public function __invoke()
    {
        $user = Auth::user();

        return $this->userService->clockOut($user);
    }
}
