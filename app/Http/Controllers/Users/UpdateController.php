<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UpdateRequest;
use App\Models\User;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke(User $user, UpdateRequest $request)
    {
        $this->usersService->edit($user, $request->validated());
        
        return $user;
    }
}
