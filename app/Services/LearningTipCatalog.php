<?php

namespace App\Services;

class LearningTipCatalog
{
    /** @var list<string> */
    public const TIPS = [
        'Break study sessions into 25-minute focused blocks with short breaks.',
        'Explain a topic out loud as if teaching a friend—gaps become obvious.',
        'Review flashcards spaced over days instead of cramming in one night.',
        'After reading, recall the main ideas without peeking—it beats rereading for retention.',
        'Turn one textbook section into five quick questions—then answer them.',
        'Sleep before a cram session hurts more than it helps schedule rest deliberately.',
        'Sketch a diagram for abstract topics—spatial memory speeds recall.',
        'Quiz yourself immediately after correcting mistakes reinforces the fix.',
        'Alternate similar subjects to avoid interference between overlapping facts.',
        'Prioritize weakest subtopics first when planning a study block.',
    ];

    public static function random(): string
    {
        return self::TIPS[array_rand(self::TIPS)];
    }
}
