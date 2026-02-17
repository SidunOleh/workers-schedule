<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerEvent extends Model
{
    protected $fillable = [
        'start',
        'end',
        'worker_id',
    ];

    protected $casts = [
        'start' => 'date:Y-m-d H:i:s',
        'end' => 'date:Y-m-d H:i:s',
    ];

    protected $with = [
        'worker',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }
}
