<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\Workers\WorkersService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GetUnavailableDaysController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke(Worker $worker, Request $request)
    {
        $start = Carbon::createFromFormat('Y-m-d', $request->query('start'));
        $end = Carbon::createFromFormat('Y-m-d', $request->query('end'));

        return $this->workersService->getUnavailableDays($worker, $start, $end);
    }
}
