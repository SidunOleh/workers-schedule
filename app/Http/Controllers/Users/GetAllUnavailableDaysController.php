<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\Users\UsersService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetAllUnavailableDaysController extends Controller
{
    public function __construct(
        public UsersService $usersService
    )
    {

    }

    public function __invoke(Request $request)
    {
        $start = Carbon::createFromFormat('Y-m-d', $request->query('start'));
        $end = Carbon::createFromFormat('Y-m-d', $request->query('end'));

        return $this->usersService->getUnavailableDays(null, $start, $end);
    }
}
