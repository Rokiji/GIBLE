<?php

namespace App\Services;

use App\Support\TopicQuerySanitizer;
use Illuminate\Support\Facades\Log;

/**
 * Integration Services Component: orchestrates external APIs and normalizes responses.
 */
class IntegrationService
{
    public function __construct(
        private WikipediaService $wikipedia,
        private YouTubeService $youtube,
    ) {}

    /**
     * @return array{
     *   ok: bool,
     *   topic: string,
     *   wikipedia: ?array,
     *   youtube: array{videos: list, error: ?string},
     *   recommendations: list<string>,
     *   errors: list<string>
     * }
     */
    public function searchLearningTopic(string $topic, array $recommendationTopics = []): array
    {
        $normalized = TopicQuerySanitizer::sanitize($topic);
        if ($normalized === '') {
            return [
                'ok' => false,
                'topic' => '',
                'wikipedia' => null,
                'youtube' => ['videos' => [], 'error' => null],
                'recommendations' => $recommendationTopics,
                'errors' => ['Enter a topic to search.'],
            ];
        }

        $wikiResult = $this->wikipedia->fetchSummary($normalized);

        $ytKey = config('services.youtube.key');
        $ytResult = $ytKey
            ? $this->youtube->searchVideos($normalized, 8)
            : ['ok' => false, 'error' => 'not_configured', 'videos' => []];

        if (! $wikiResult['ok'] && filled($wikiResult['error'])) {
            Log::warning('Wikipedia summary unavailable for topic search.', [
                'query_len' => function_exists('mb_strlen') ? mb_strlen($normalized, 'UTF-8') : strlen($normalized),
                'internal_reason' => $wikiResult['error'],
            ]);
        }

        $errors = [];

        $resolvedTopic = $wikiResult['data']['title'] ?? $normalized;

        $youtubeClientError = $ytResult['ok']
            ? null
            : $this->youtubeUserMessage(($ytResult['error'] ?? '') === 'not_configured' ? 'not_configured' : (string) ($ytResult['error'] ?? ''));

        return [
            'ok' => $wikiResult['ok'] || $ytResult['ok'],
            'topic' => $resolvedTopic,
            'wikipedia' => $wikiResult['data'],
            'youtube' => [
                'videos' => $ytResult['videos'],
                'error' => $youtubeClientError,
            ],
            'recommendations' => $recommendationTopics,
            'errors' => $errors,
        ];
    }

    /**
     * Lightweight enrichment for dashboard cards (summary + one video).
     *
     * @return array{topic: string, wikipedia: ?array, youtube_video: ?array, errors: list<string>}
     */
    public function enrichRecommendationTopic(string $label): array
    {
        $clean = TopicQuerySanitizer::sanitize($label);
        if ($clean === '') {
            return [
                'topic' => '',
                'wikipedia' => null,
                'youtube_video' => null,
                'errors' => ['Invalid topic label.'],
            ];
        }

        $wiki = $this->wikipedia->fetchSummary($clean);
        $yt = $this->youtube->searchVideos($clean, 1);
        if (! $wiki['ok'] && filled($wiki['error'])) {
            Log::warning('Wikipedia summary unavailable for recommendation card.', [
                'query_len' => function_exists('mb_strlen') ? mb_strlen($clean, 'UTF-8') : strlen($clean),
                'internal_reason' => $wiki['error'],
            ]);
        }
        $errors = [];
        if (! $yt['ok']) {
            $errors[] = 'Sample video could not be loaded.';
        }

        return [
            'topic' => $wiki['data']['title'] ?? $clean,
            'wikipedia' => $wiki['data'],
            'youtube_video' => $yt['videos'][0] ?? null,
            'errors' => $errors,
        ];
    }

    private function youtubeUserMessage(string $code): string
    {
        return match ($code) {
            'not_configured' => 'Educational videos are not configured for this environment. Add YOUTUBE_API_KEY to your .env file.',
            'youtube_quota' => 'YouTube daily quota is exceeded. Try again tomorrow or raise the quota in Google Cloud Console.',
            'youtube_key' => 'YouTube rejected the API key. Enable YouTube Data API v3 for the key’s project and check restrictions.',
            'youtube_bad_request' => 'YouTube could not run this search. Check that the API key and YouTube Data API v3 are set up correctly.',
            'network' => 'Could not reach YouTube. Check your server’s internet connection and try again.',
            default => str_starts_with($code, 'youtube_http_')
                ? 'YouTube returned an error (HTTP). Verify YOUTUBE_API_KEY and API access in Google Cloud.'
                : 'Educational videos could not be loaded right now.',
        };
    }
}
