<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke(User $user)
    {
        $this->usersService->delete($user);

        return response('OK');
    }
}
