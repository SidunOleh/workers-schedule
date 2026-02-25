<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerEvent extends Model
{
    protected $fillable = [
        'start',
        'end',
        'user_id',
        'type',
    ];

    protected $casts = [
        'start' => 'date:Y-m-d H:i:s',
        'end' => 'date:Y-m-d H:i:s',
    ];

    protected $with = [
        'user',
    ];

    public const PLANED = 'planed';

    public const REAL = 'real';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
