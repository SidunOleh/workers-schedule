<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Services\Events\EventsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CopyController extends Controller
{
   public function __construct(
        public EventsService $eventsService
    )
    {

    }

    public function __invoke(Request $request)
    {
        $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);
        $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->end);

        $this->eventsService->copy($start, $end);

        return response('OK');
    }
}
