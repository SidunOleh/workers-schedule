<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Services\Workers\WorkersService;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke(Worker $worker)
    {
        $this->workersService->delete($worker);

        return response('OK');
    }
}
