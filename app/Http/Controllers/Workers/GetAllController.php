<?php

namespace App\Http\Controllers\Workers;

use App\Http\Controllers\Controller;
use App\Services\Workers\WorkersService;
use Illuminate\Http\Request;

class GetAllController extends Controller
{
    public function __construct(
        public WorkersService $workersService
    )
    {

    }

    public function __invoke()
    {
        return $this->workersService->getAll();
    }
}
