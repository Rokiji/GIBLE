<?php

namespace App\Services;

use App\Support\TopicQuerySanitizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeService
{
    private const SEARCH = 'https://www.googleapis.com/youtube/v3/search';

    /**
     * @return array{ok: bool, error: ?string, videos: list<array>}
     */
    public function searchVideos(string $query, int $maxResults = 8): array
    {
        $key = config('services.youtube.key');
        if (! $key) {
            return ['ok' => false, 'error' => null, 'videos' => []];
        }

        $sanitized = TopicQuerySanitizer::sanitize($query);
        if ($sanitized === '') {
            return ['ok' => false, 'error' => null, 'videos' => []];
        }

        try {
            $response = Http::timeout(15)->get(self::SEARCH, [
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => $maxResults,
                'q' => $sanitized.' tutorial education',
                'key' => $key,
            ]);

            if (! $response->successful()) {
                $hint = $this->youtubeApiUserHint($response->status(), $response->json('error'));
                Log::warning('YouTube Data API search failed.', [
                    'http_status' => $response->status(),
                    'hint' => $hint,
                ]);

                return ['ok' => false, 'error' => $hint, 'videos' => []];
            }
            $data = $response->json();
            $items = $data['items'] ?? [];
            $videos = [];
            foreach ($items as $item) {
                $videos[] = [
                    'id' => $item['id']['videoId'] ?? null,
                    'title' => $item['snippet']['title'] ?? '',
                    'channelTitle' => $item['snippet']['channelTitle'] ?? '',
                    'description' => $item['snippet']['description'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['medium']['url']
                        ?? $item['snippet']['thumbnails']['default']['url'] ?? null,
                    'publishedAt' => $item['snippet']['publishedAt'] ?? null,
                ];
            }

            return ['ok' => true, 'error' => null, 'videos' => $videos];
        } catch (\Throwable $e) {
            Log::warning('YouTube Data API search exception.', ['message' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'network', 'videos' => []];
        }
    }

    /**
     * Short, safe message for UI (no secrets). Maps common API failures.
     *
     * @param  array<string, mixed>|null  $errorBody
     */
    private function youtubeApiUserHint(int $status, ?array $errorBody): string
    {
        $reason = $errorBody['errors'][0]['reason'] ?? null;
        $message = isset($errorBody['message']) ? (string) $errorBody['message'] : '';

        if ($status === 403 && (str_contains($message, 'quota') || $reason === 'quotaExceeded')) {
            return 'youtube_quota';
        }
        if ($status === 403 && ($reason === 'forbidden' || str_contains(strtolower($message), 'api key'))) {
            return 'youtube_key';
        }
        if ($status === 400) {
            return 'youtube_bad_request';
        }

        return 'youtube_http_'.$status;
    }
}
