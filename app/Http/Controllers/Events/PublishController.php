<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Services\Events\EventsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function __construct(
        public EventsService $eventsService
    )
    {

    }

    public function __invoke(Request $request)
    {
        $date = Carbon::createFromFormat('Y-m-d', $request->date);

        $this->eventsService->publish($date);

        return response('OK');
    }
}
