<?php

namespace App\Console\Commands;

use App\Models\WorkerEvent;
use Illuminate\Console\Command;

class ClockOut extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clock-out';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clock out';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = WorkerEvent::where('type', WorkerEvent::REAL)
            ->whereNull('end')
            ->get();

        foreach ($events as $event) {
            if ($event->start->day == now()->day and now()->hour < 22) {
                continue;
            }

            $end = $event->start->setHour(22)->setMinutes(0);
            $event->update([
                'end' => $end,
            ]);
        }
    }
}
