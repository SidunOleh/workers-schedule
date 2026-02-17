<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workers\UpdateRequest;
use App\Models\Worker;
use App\Services\Workers\WorkersService;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke(Worker $worker, UpdateRequest $request)
    {
        return $this->workersService->edit($worker, $request->validated());
    }
}
