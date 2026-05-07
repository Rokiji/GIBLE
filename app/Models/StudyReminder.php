<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudyReminder extends Model
{
    protected $fillable = ['user_id', 'subject', 'remind_at', 'notified_at'];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
