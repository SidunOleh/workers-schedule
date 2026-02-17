<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Models\WorkerEvent;
use App\Services\Events\EventsService;
use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function __construct(
        public EventsService $eventsService
    )
    {

    }

    public function __invoke(WorkerEvent $workerEvent)
    {
        $this->eventsService->delete($workerEvent);

        return response('OK');
    }
}
