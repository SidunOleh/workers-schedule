<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\ChangeUnavailableDaysRequest;
use App\Models\User;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;

class ChangeUnavailableDaysController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke(User $user, ChangeUnavailableDaysRequest $request)
    {
        return $this->usersService->changeUnavailableDays($user, $request->days);
    }
}
