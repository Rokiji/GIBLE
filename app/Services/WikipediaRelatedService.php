<?php

namespace App\Services;

use App\Support\TopicQuerySanitizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Fetches linguistically/topically related Wikipedia page titles via the REST API.
 */
class WikipediaRelatedService
{
    private const RELATED_BASE = 'https://en.wikipedia.org/api/rest_v1/page/related/';

    private const USER_AGENT = 'GIBLE/1.0 (https://localhost; educational course project) Laravel';

    /** @var positive-int */
    private const TTL_SECONDS = 3600;

    /**
     * @return list<string>
     */
    public function relatedTitles(string $topic, int $limit = 12): array
    {
        $trimmed = TopicQuerySanitizer::sanitize($topic);
        if ($trimmed === '') {
            return [];
        }

        $limit = max(1, min(20, $limit));
        $slug = str_replace(' ', '_', $trimmed);
        $cacheKey = 'giblewiki:related:'.substr(hash('sha256', mb_strtolower($slug)), 0, 40);

        $titles = Cache::remember($cacheKey, self::TTL_SECONDS, function () use ($slug) {
            $url = self::RELATED_BASE.rawurlencode($slug);

            try {
                $response = Http::timeout(12)
                    ->withHeaders(['User-Agent' => self::USER_AGENT])
                    ->get($url);

                if (! $response->successful()) {
                    return [];
                }

                $data = $response->json();
                if (! is_array($data)) {
                    return [];
                }

                /** @var list<string> $out */
                $out = [];

                foreach ($data['pages'] ?? [] as $page) {
                    if (! is_array($page)) {
                        continue;
                    }
                    $label = null;
                    if (isset($page['titles']) && is_array($page['titles'])) {
                        $label = $page['titles']['normalized']
                            ?? $page['titles']['display']
                            ?? $page['titles']['canonical']
                            ?? null;
                    }
                    $label = $label ?? ($page['title'] ?? null);
                    if ($label !== null && is_string($label) && trim($label) !== '') {
                        $out[] = trim($label);
                    }
                }

                return array_values(array_unique($out));
            } catch (\Throwable) {
                return [];
            }
        });

        return array_slice($titles, 0, $limit);
    }
}
