<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreRequest;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke(StoreRequest $request)
    {
        return $this->usersService->create($request->validated());
    }
}
