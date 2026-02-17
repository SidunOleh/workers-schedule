<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workers\ChangeUnavailableDaysRequest;
use App\Models\Worker;
use App\Services\Workers\WorkersService;
use Illuminate\Http\Request;

class ChangeUnavailableDaysController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke(Worker $worker, ChangeUnavailableDaysRequest $request)
    {
        return $this->workersService->changeUnavailableDays($worker, $request->days);
    }
}
