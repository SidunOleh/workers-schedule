<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workers\StoreRequest;
use App\Services\Workers\WorkersService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke(StoreRequest $request)
    {
        return $this->workersService->create($request->validated());
    }
}
