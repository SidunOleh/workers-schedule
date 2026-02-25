<?php

namespace App\Http\Controllers\Events;

use App\Exceptions\TimeIsUnavailableForWorkerException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Events\StoreRequest;
use App\Models\User;
use App\Services\Events\EventsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        public EventsService $eventsService
    )
    {

    }

    public function __invoke(StoreRequest $request)
    {
        try {
            $user = User::find($request->user_id);
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $request->start);
            $end = Carbon::createFromFormat('Y-m-d H:i:s', $request->end);

            $event = $this->eventsService->create($user, $start, $end);

            $event->load('user');

            return $event;
        } catch (TimeIsUnavailableForWorkerException $e) {
            return response(['message' => 'Worker is unavailable.'], 403);
        }
    }
}
