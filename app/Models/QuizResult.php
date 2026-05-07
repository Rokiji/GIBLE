<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizResult extends Model
{
    protected $fillable = ['user_id', 'topic_id', 'topic', 'score', 'total'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function learningTopic(): BelongsTo
    {
        return $this->belongsTo(LearningTopic::class, 'topic_id');
    }
}
