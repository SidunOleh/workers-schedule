<?php

namespace App\Services\Users;

use App\Exceptions\UserHasUnclosedEventException;
use App\Models\UnavailableDay;
use App\Models\User;
use App\Models\WorkerEvent;
use App\Services\Events\EventsService;
use Carbon\Carbon;

class UsersService
{
    public function getWorkers()
    {
        return User::where('role', User::WORKER)->get();
    }

    public function create(array $data): User
    {
        return User::create([
            'email' => $data['email'],
            'password' => $data['password'],
            'name' => $data['name'],
            'color' => $data['color'],
            'role' => $data['role'],
        ]);
    }

    public function delete(User $user)
    {
        $user->delete();
    }

    public function isAvailableTimeRage(User $user, Carbon $start, ?Carbon $end = null): bool
    {
        $end = $end ?? $start;

        $startStr = $start->format('Y-m-d H:i:s');
        $endStr   = $end->format('Y-m-d H:i:s');

        $unavailable = UnavailableDay::where('user_id', $user->id)
            ->whereRaw("
                TIMESTAMP(`date`, COALESCE(`unavailable_from`, '00:00:00')) < ?
                AND
                TIMESTAMP(`date`, COALESCE(`unavailable_to`, '23:59:59')) > ?
            ", [$endStr, $startStr])
            ->exists();

            return ! $unavailable;
    }

    public function getUnavailableDays(?User $user, Carbon $start, Carbon $end)
    {
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        $q = UnavailableDay::whereBetween('date', [$startStr, $endStr]);

        if ($user) {
            $q->where('user_id', $user->id);
        }

        return $q->get();
    }

    public function changeUnavailableDays(User $user, array $days)
    {
        foreach ($days as $day) {
            if ($day['unavailable']) {
                UnavailableDay::updateOrCreate([
                    'user_id' => $user->id,
                    'date' => $day['date'],
                ], [
                    'unavailable_from' => $day['unavailable_from'],
                    'unavailable_to' => $day['unavailable_to'],
                ]);
            } else {
                UnavailableDay::where([
                    'user_id' => $user->id,
                    'date' => $day['date'],
                ])->delete();
            }
        }
    }

    public function clockIn(User $user): WorkerEvent
    {
        $unclosed = $this->getClockIn($user);

        if ($unclosed ) {
            return $unclosed;
        }

        $eventsService = app(EventsService::class);

        return $eventsService->create($user, Carbon::now(), null, WorkerEvent::REAL);
    }

    public function getClockIn(User $user): ?WorkerEvent
    {
        $eventsService = app(EventsService::class);

        return $eventsService->getUnclosedEvent($user);
    }

    public function clockOut(User $user): ?WorkerEvent
    {
        $eventsService = app(EventsService::class);

        $unclosed = $eventsService->getUnclosedEvent($user);

        if (! $unclosed) {
            return null;
        }

        $eventsService->edit($unclosed, $user, $unclosed->start, Carbon::now());

        return $unclosed;
    }
}