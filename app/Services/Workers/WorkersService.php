<?php

namespace App\Services\Workers;

use App\Models\UnavailableDay;
use App\Models\Worker;
use Carbon\Carbon;

class WorkersService
{
    public function getAll()
    {
        return Worker::all();
    }

    public function create(array $data): Worker
    {
        return Worker::create([
            'name' => $data['name'],
            'color' => $data['color'],
        ]);
    }

    public function edit(Worker $worker, array $data)
    {
        $worker->update([
            'name' => $data['name'],
            'color' => $data['color'],
        ]);
    }

    public function delete(Worker $worker)
    {
        $worker->delete();
    }

    public function isAvailableTimeRage(Worker $worker, Carbon $start, Carbon $end): bool
    {
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr   = $end->format('Y-m-d H:i:s');

        $unavailable = UnavailableDay::where('worker_id', $worker->id)
            ->whereRaw("
                STR_TO_DATE(
                    CONCAT(`date`, ' ', COALESCE(`unavailable_from`, '00:00:00')),
                    '%Y-%m-%d %H:%i:%s'
                ) <= ?
                AND
                STR_TO_DATE(
                    CONCAT(`date`, ' ', COALESCE(`unavailable_to`, '23:59:59')),
                    '%Y-%m-%d %H:%i:%s'
                ) >= ?
            ", [$endStr, $startStr])->exists();

        return ! $unavailable;
    }

    public function getUnavailableDays(Worker $worker, Carbon $start, Carbon $end)
    {
        $startStr = $start->format('Y-m-d');
        $endStr = $end->format('Y-m-d');

        return UnavailableDay::where('worker_id', $worker->id)
            ->whereBetween('date', [$startStr, $endStr])
            ->get();
    }

    public function changeUnavailableDays(worker $worker, array $days)
    {
        foreach ($days as $day) {
            if ($day['unavailable']) {
                UnavailableDay::updateOrCreate([
                    'worker_id' => $worker->id,
                    'date' => $day['date'],
                ], [
                    'unavailable_from' => $day['unavailable_from'],
                    'unavailable_to' => $day['unavailable_to'],
                ]);
            } else {
                UnavailableDay::where([
                    'worker_id' => $worker->id,
                    'date' => $day['date'],
                ])->delete();
            }
        }
    }
}