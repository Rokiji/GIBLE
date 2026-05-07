<?php

namespace App\Services;

use App\Models\LearningTopic;
use Illuminate\Support\Str;

class TopicCatalogService
{
    public function resolveId(string $label): ?int
    {
        $normalized = trim($label);
        if ($normalized === '') {
            return null;
        }

        $slug = Str::of($normalized)->lower()->slug(' ')->replace(' ', '-')->value();
        if ($slug === '') {
            return null;
        }

        $topic = LearningTopic::firstOrCreate(
            ['slug' => $slug],
            ['label' => Str::title($normalized)]
        );

        return $topic->id;
    }
}
