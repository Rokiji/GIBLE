<?php

namespace App\Services;

use App\Support\TopicQuerySanitizer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WikipediaService
{
    private const REST_SUMMARY = 'https://en.wikipedia.org/api/rest_v1/page/summary';

    private const MEDIAWIKI_API = 'https://en.wikipedia.org/w/api.php';

    /**
     * @return array{ok: bool, error: ?string, data: ?array{title: string, extract: string, description: string, thumbnail: ?string, contentUrls: ?string}}
     */
    public function fetchSummary(string $topic): array
    {
        $query = TopicQuerySanitizer::sanitize($topic);
        if ($query === '') {
            return ['ok' => false, 'error' => null, 'data' => null];
        }

        $cacheKey = 'giblewiki:summary:'.substr(hash('sha256', mb_strtolower($query)), 0, 40);
        $cached = Cache::get($cacheKey);

        if (is_array($cached) && ($cached['ok'] ?? false) === true) {
            return ['ok' => true, 'error' => null, 'data' => $cached['data'] ?? null];
        }

        [$ok, $data, $status] = $this->restSummary($query);
        if ($ok && is_array($data)) {
            $normalized = $this->normalizeWikiData($data, $query);
            if (trim($normalized['extract']) === '') {
                $normalized = $this->fallbackSummary($query);
            }
            $out = ['ok' => true, 'error' => null, 'data' => $normalized];
            Cache::put($cacheKey, $out, $this->cacheTtlSeconds());

            return $out;
        }

        if ($status === 404) {
            $guess = $this->bestTitleFromSearch($query);
            if ($guess !== null && mb_strtolower((string) $guess) !== mb_strtolower($query)) {
                [$ok2, $data2] = $this->restSummary((string) $guess);
                if ($ok2 && is_array($data2)) {
                    $normalized = $this->normalizeWikiData($data2, (string) $guess);
                    if (trim($normalized['extract']) === '') {
                        $normalized = $this->fallbackSummary((string) $guess);
                    }
                    $out = ['ok' => true, 'error' => null, 'data' => $normalized];
                    Cache::put($cacheKey, $out, $this->cacheTtlSeconds());

                    return $out;
                }
            }

            $out = ['ok' => true, 'error' => null, 'data' => $this->fallbackSummary($query)];
            Cache::put($cacheKey, $out, $this->cacheTtlSeconds());

            return $out;
        }

        if ($status === 429) {
            return ['ok' => true, 'error' => null, 'data' => $this->fallbackSummary($query)];
        }

        if ($status === 503) {
            return ['ok' => true, 'error' => null, 'data' => $this->fallbackSummary($query)];
        }

        return ['ok' => true, 'error' => null, 'data' => $this->fallbackSummary($query)];
    }

    /**
     * @return array{0: bool, 1: ?array, 2: int|null}
     */
    private function restSummary(string $title): array
    {
        $segment = rawurlencode(str_replace(' ', '_', trim($title)));

        try {
            $response = $this->getWithBackoff(self::REST_SUMMARY.'/'.$segment);

            $status = $response->status();

            if ($status === 404) {
                return [false, null, 404];
            }

            if (! $response->successful()) {
                return [false, null, $status];
            }

            $data = $response->json();
            if (! is_array($data)) {
                return [false, null, $status];
            }

            return [true, $data, $status];
        } catch (\Throwable) {
            return [false, null, 0];
        }
    }

    private function bestTitleFromSearch(string $query): ?string
    {
        try {
            $response = Http::timeout($this->timeoutSeconds())
                ->withHeaders(['User-Agent' => $this->userAgent()])
                ->get(self::MEDIAWIKI_API, [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $query,
                    'srlimit' => 5,
                    'format' => 'json',
                    'srnamespace' => '0',
                ]);

            if (! $response->successful()) {
                return null;
            }

            $hits = $response->json('query.search');
            if (! is_array($hits) || $hits === []) {
                return null;
            }

            foreach ($hits as $hit) {
                if (! is_array($hit)) {
                    continue;
                }
                $t = $hit['title'] ?? null;
                if (is_string($t)) {
                    $t = trim($t);
                    if ($t !== '') {
                        return $t;
                    }
                }
            }
        } catch (\Throwable) {
        }

        return null;
    }

    private function userAgent(): string
    {
        return (string) config('services.wikipedia.user_agent', 'GIBLE/1.0 (educational project)');
    }

    private function timeoutSeconds(): int
    {
        return max(3, (int) config('services.wikipedia.timeout_seconds', 12));
    }

    private function cacheTtlSeconds(): int
    {
        return max(60, (int) config('services.wikipedia.cache_ttl_seconds', 1800));
    }

    private function maxRetries(): int
    {
        return max(0, (int) config('services.wikipedia.max_retries', 2));
    }

    private function getWithBackoff(string $url)
    {
        $attempt = 0;
        $maxAttempts = $this->maxRetries() + 1;
        $response = Http::timeout($this->timeoutSeconds())
            ->withHeaders(['User-Agent' => $this->userAgent()])
            ->get($url);

        while ($response->status() === 429 || $response->status() === 503) {
            $attempt++;
            if ($attempt >= $maxAttempts) {
                break;
            }
            usleep((250 * (2 ** ($attempt - 1))) * 1000);
            $response = Http::timeout($this->timeoutSeconds())
                ->withHeaders(['User-Agent' => $this->userAgent()])
                ->get($url);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{title: string, extract: string, description: string, thumbnail: ?string, contentUrls: ?string}
     */
    private function normalizeWikiData(array $data, string $fallbackTitle): array
    {
        $thumb = $data['thumbnail'] ?? null;
        $urls = $data['content_urls'] ?? null;

        return [
            'title' => (string) ($data['title'] ?? $fallbackTitle),
            'extract' => (string) ($data['extract'] ?? ''),
            'description' => (string) ($data['description'] ?? ''),
            'thumbnail' => is_array($thumb) && isset($thumb['source']) ? (string) $thumb['source'] : null,
            'contentUrls' => is_array($urls) && isset($urls['desktop']['page']) ? (string) $urls['desktop']['page'] : null,
        ];
    }

    /**
     * @return array{title: string, extract: string, description: string, thumbnail: ?string, contentUrls: ?string}
     */
    private function fallbackSummary(string $topic): array
    {
        $title = trim($topic) !== '' ? $topic : 'Topic';

        return [
            'title' => $title,
            'extract' => "Overview: {$title} is an important concept in computing and digital learning. Use this as a study starter, then refine the topic keywords for more specific references.",
            'description' => 'Generated study overview',
            'thumbnail' => null,
            'contentUrls' => 'https://en.wikipedia.org/wiki/'.rawurlencode(str_replace(' ', '_', $title)),
        ];
    }
}
