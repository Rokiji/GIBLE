<?php

namespace Tests\Unit;

use App\Services\IntegrationService;
use App\Services\WikipediaService;
use App\Services\YouTubeService;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class IntegrationServiceSearchTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_empty_topic_does_not_call_apis_or_log(): void
    {
        Event::fake([MessageLogged::class]);

        $wiki = Mockery::mock(WikipediaService::class);
        $wiki->shouldReceive('fetchSummary')->never();

        $yt = Mockery::mock(YouTubeService::class);
        $yt->shouldReceive('searchVideos')->never();

        config(['services.youtube.key' => 'x']);

        $svc = new IntegrationService($wiki, $yt);
        $result = $svc->searchLearningTopic('   ', []);

        $this->assertSame(['Enter a topic to search.'], $result['errors']);
        Event::assertNotDispatched(MessageLogged::class);
    }

    public function test_wikipedia_failure_is_logged_not_sent_to_client(): void
    {
        Event::fake([MessageLogged::class]);
        config(['services.youtube.key' => 'test-key']);

        $wiki = Mockery::mock(WikipediaService::class);
        $wiki->shouldReceive('fetchSummary')->once()->with('foo')->andReturn([
            'ok' => false,
            'error' => 'internal-detail-not-for-browser',
            'data' => null,
        ]);

        $yt = Mockery::mock(YouTubeService::class);
        $yt->shouldReceive('searchVideos')->once()->with('foo', 8)->andReturn([
            'ok' => true,
            'error' => null,
            'videos' => [['id' => 'abc']],
        ]);

        $svc = new IntegrationService($wiki, $yt);
        $result = $svc->searchLearningTopic('foo', []);

        $this->assertSame([], $result['errors']);
        $this->assertStringNotContainsString('internal-detail', json_encode($result));

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $e) {
            return $e->level === 'warning'
                && str_contains($e->message, 'Wikipedia summary unavailable for topic search.')
                && ($e->context['internal_reason'] ?? null) === 'internal-detail-not-for-browser';
        });
    }

    public function test_wikipedia_success_does_not_emit_summary_warning(): void
    {
        Event::fake([MessageLogged::class]);
        config(['services.youtube.key' => 'test-key']);

        $wiki = Mockery::mock(WikipediaService::class);
        $wiki->shouldReceive('fetchSummary')->andReturn([
            'ok' => true,
            'error' => null,
            'data' => [
                'title' => 'Foo',
                'extract' => 'Body',
                'description' => '',
                'thumbnail' => null,
                'contentUrls' => null,
            ],
        ]);

        $yt = Mockery::mock(YouTubeService::class);
        $yt->shouldReceive('searchVideos')->andReturn(['ok' => true, 'error' => null, 'videos' => []]);

        $svc = new IntegrationService($wiki, $yt);
        $svc->searchLearningTopic('foo', []);

        Event::assertNotDispatched(MessageLogged::class);
    }

    public function test_enrich_does_not_put_wiki_messages_in_errors_array(): void
    {
        Event::fake([MessageLogged::class]);
        config(['services.youtube.key' => 'test-key']);

        $wiki = Mockery::mock(WikipediaService::class);
        $wiki->shouldReceive('fetchSummary')->andReturn([
            'ok' => false,
            'error' => 'secret-wiki-reason',
            'data' => null,
        ]);

        $yt = Mockery::mock(YouTubeService::class);
        $yt->shouldReceive('searchVideos')->andReturn([
            'ok' => true,
            'error' => null,
            'videos' => [['id' => 'v1', 'title' => 'T', 'channelTitle' => 'C']],
        ]);

        $svc = new IntegrationService($wiki, $yt);
        $result = $svc->enrichRecommendationTopic('Algebra');

        $this->assertSame([], $result['errors']);
        $this->assertStringNotContainsString('secret-wiki', json_encode($result));

        Event::assertDispatched(MessageLogged::class, function (MessageLogged $e) {
            return str_contains($e->message, 'recommendation card.')
                && ($e->context['internal_reason'] ?? null) === 'secret-wiki-reason';
        });
    }
}
