<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UsersService;
use Illuminate\Http\Request;

class GetWorkersController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke()
    {
        return $this->usersService->getWorkers();
    }
}
