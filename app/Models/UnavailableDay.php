<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnavailableDay extends Model
{
    protected $fillable = [
        'date',
        'user_id',
        'unavailable_from',
        'unavailable_to',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'unavailable_from' => 'date:H:i',
        'unavailable_to' => 'date:H:i',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
