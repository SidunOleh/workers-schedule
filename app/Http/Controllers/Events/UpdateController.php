<?php

namespace App\Http\Controllers\Events;

use App\Exceptions\TimeIsUnavailableForWorkerException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\UpdateRequest;
use App\Models\Worker;
use App\Models\WorkerEvent;
use App\Services\Events\EventsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UpdateController extends Controller
{
    public function __construct(
        public EventsService $eventsService
    )
    {

    }

    public function __invoke(WorkerEvent $workerEvent, UpdateRequest $request)
    {
        try {
            $worker = Worker::find($request->worker_id);
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);
            $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->end);

            $this->eventsService->edit($workerEvent, $worker, $start, $end);

            $workerEvent->refresh();

            return $workerEvent;
        } catch (TimeIsUnavailableForWorkerException $e) {
            return response(['message' => 'Worker is unavailable.'], 403);
        }
    }
}
