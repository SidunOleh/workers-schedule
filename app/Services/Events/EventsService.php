<?php

namespace App\Services\Events;

use App\Exceptions\TimeIsUnavailableForWorkerException;
use App\Models\User;
use App\Models\WorkerEvent;
use App\Services\Users\UsersService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class EventsService
{
    public function __construct(
        private UsersService $usersService
    )
    {

    }

    public function get(Carbon $start, Carbon $end)
    {
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        
        return WorkerEvent::where(function ($q) use($startStr, $endStr) {
            $q->whereBetween('start', [$startStr, $endStr])
            ->orWhereBetween('end', [$startStr, $endStr]);
        })->whereNotNull('end')->get();
    }

    public function create(User $user, Carbon $start, ?Carbon $end = null, $type = WorkerEvent::PLANED): WorkerEvent
    {
        if (! $this->usersService->isAvailableTimeRage($user, $start, $end)) {
            throw new TimeIsUnavailableForWorkerException();
        }

        return WorkerEvent::create([
            'user_id' => $user->id,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end?->format('Y-m-d H:i:s') ?? null,
            'type' => $type,
        ]);
    }

    public function edit(WorkerEvent $userEvent, User $user, Carbon $start, Carbon $end)
    {
        if (! $this->usersService->isAvailableTimeRage($user, $start, $end)) {
            throw new TimeIsUnavailableForWorkerException();
        }

        $userEvent->update([
            'user_id' => $user->id,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(WorkerEvent $userEvent)
    {
        $userEvent->delete();
    }

    public function clear(Carbon $start, Carbon $end): void
    {
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');

        WorkerEvent::whereBetween('start', [$startStr, $endStr])
            ->orWhereBetween('end', [$startStr, $endStr])
            ->where('type', WorkerEvent::PLANED)
            ->delete();
    }

    public function copy(Carbon $start, Carbon $end): void
    {
        $this->clear($start, $end);

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $prevDate = $date->copy()->subWeek();

            $events = WorkerEvent::where(DB::raw('DATE(start)'), $prevDate->format('Y-m-d'))
                ->where('type', WorkerEvent::PLANED)
                ->get();

            foreach ($events as $event) {
                $newEvent = $event->replicate();
                $newEvent->start = $newEvent->start->addWeek();
                $newEvent->end = $newEvent->end->addWeek();

                if ($this->usersService->isAvailableTimeRage(
                    $newEvent->user, 
                    $newEvent->start, 
                    $newEvent->end)
                ) {
                    $newEvent->save();
                }
            }
        }
    }

    public function getUnclosedEvent(User $user): ?WorkerEvent
    {
        return WorkerEvent::whereNull('end')->where('user_id', $user->id)->first();
    }
}