<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningTopic extends Model
{
    protected $fillable = ['slug', 'label'];

    public function searches(): HasMany
    {
        return $this->hasMany(SearchHistory::class, 'topic_id');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class, 'topic_id');
    }

    public function flashDecks(): HasMany
    {
        return $this->hasMany(FlashcardDeck::class, 'topic_id');
    }
}
