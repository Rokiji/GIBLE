<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    protected $fillable = [
        'user_id',
        'service',
        'event_type',
        'delivered',
        'attempts',
        'http_status',
        'error_message',
        'delivered_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'delivered' => 'boolean',
            'delivered_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
