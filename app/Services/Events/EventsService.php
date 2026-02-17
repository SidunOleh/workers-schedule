<?php

namespace App\Services\Events;

use App\Exceptions\TimeIsUnavailableForWorkerException;
use App\Models\Worker;
use App\Models\WorkerEvent;
use App\Services\Workers\WorkersService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class EventsService
{
    public function __construct(
        private WorkersService $workersService
    )
    {

    }

    public function get(Carbon $start, Carbon $end)
    {
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        
        return WorkerEvent::whereBetween('start', [$startStr, $endStr])
            ->orWhereBetween('end', [$startStr, $endStr])
            ->get();
    }

    public function create(Worker $worker, Carbon $start, Carbon $end): WorkerEvent
    {
        if (! $this->workersService->isAvailableTimeRage($worker, $start, $end)) {
            throw new TimeIsUnavailableForWorkerException();
        }

        return WorkerEvent::create([
            'worker_id' => $worker->id,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ]);
    }

    public function edit(WorkerEvent $workerEvent, Worker $worker, Carbon $start, Carbon $end)
    {
        if (! $this->workersService->isAvailableTimeRage($worker, $start, $end)) {
            throw new TimeIsUnavailableForWorkerException();
        }

        $workerEvent->update([
            'worker_id' => $worker->id,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(WorkerEvent $workerEvent)
    {
        $workerEvent->delete();
    }

    public function clear(Carbon $start, Carbon $end): void
    {
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');

        WorkerEvent::whereBetween('start', [$startStr, $endStr])
            ->orWhereBetween('end', [$startStr, $endStr])->delete();
    }

    public function copy(Carbon $start, Carbon $end): void
    {
        $this->clear($start, $end);

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $prevDate = $date->copy()->subWeek();

            $events = WorkerEvent::where(DB::raw('DATE(start)'), $prevDate->format('Y-m-d'))->get();

            foreach ($events as $event) {
                $newEvent = $event->replicate();
                $newEvent->start = $newEvent->start->addWeek();
                $newEvent->end = $newEvent->end->addWeek();

                if ($this->workersService->isAvailableTimeRage(
                    $newEvent->worker, 
                    $newEvent->start, 
                    $newEvent->end)
                ) {
                    $newEvent->save();
                }
            }
        }
    }
}