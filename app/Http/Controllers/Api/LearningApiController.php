<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FlashcardDeck;
use App\Models\QuizResult;
use App\Models\SearchHistory;
use App\Services\DiscordWebhookService;
use App\Services\FlashcardGenerator;
use App\Services\IntegrationService;
use App\Services\LearningTipCatalog;
use App\Services\QuizGenerator;
use App\Services\RecommendationService;
use App\Services\TopicCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LearningApiController extends Controller
{
    public function __construct(
        private IntegrationService $integration,
        private RecommendationService $recommendations,
        private QuizGenerator $quizGenerator,
        private FlashcardGenerator $flashcardGenerator,
        private DiscordWebhookService $discord,
        private TopicCatalogService $topics,
    ) {}

    public function search(Request $request): JsonResponse
    {
        $topic = (string) $request->query('topic', '');
        $user = $request->user();
        $recList = $this->recommendations->recommendTopicsForUser($user, $topic);
        $result = $this->integration->searchLearningTopic($topic, $recList);

        $hasWiki = ! empty($result['wikipedia']['extract'] ?? null);
        $hasVideos = count($result['youtube']['videos'] ?? []) > 0;
        if ($hasWiki || $hasVideos) {
            $snippet = $hasWiki
                ? mb_substr((string) $result['wikipedia']['extract'], 0, 500)
                : 'Video-focused session (summary unavailable).';
            SearchHistory::create([
                'user_id' => $user->id,
                'topic_id' => $this->topics->resolveId($result['topic']),
                'topic' => $result['topic'],
                'summary_snippet' => $snippet,
            ]);
            $this->discord->sendLearningTip(LearningTipCatalog::random(), $user->id, $user->name);
        }

        return response()->json($result);
    }

    public function quizSubmit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:255'],
            'questions' => ['required', 'array'],
            'answers' => ['required', 'array'],
        ]);
        $questions = $data['questions'];
        $answers = $data['answers'];
        $score = 0;
        foreach ($questions as $i => $q) {
            $correct = $q['answer'] ?? null;
            $given = $answers[$i] ?? null;
            if (is_numeric($given) && isset($q['options'][(int) $given])) {
                $given = $q['options'][(int) $given];
            }
            if ($correct === $given) {
                $score++;
            }
        }
        $total = count($questions);
        $topic = (string) ($data['topic'] ?? 'Topic');

        QuizResult::create([
            'user_id' => $request->user()->id,
            'topic_id' => $this->topics->resolveId($topic),
            'topic' => $topic,
            'score' => $score,
            'total' => $total,
        ]);

        $webhook = $this->discord->sendQuizAchievement($topic, $score, $total, $request->user()->id);
        $this->discord->sendLearningTip(LearningTipCatalog::random(), $request->user()->id, $request->user()->name);

        return response()->json([
            'score' => $score,
            'total' => $total,
            'discord' => $webhook,
            'webhook_delivered' => $webhook['delivered'] ?? false,
        ]);
    }

    public function quizGenerate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:255'],
            'extract' => ['required', 'string'],
        ]);
        $topic = (string) ($data['topic'] ?? 'Topic');
        $out = $this->quizGenerator->buildQuiz($topic, $data['extract'], 10);

        return response()->json(['questions' => $out['questions']]);
    }

    public function flashcardsDeck(Request $request, int $id): JsonResponse
    {
        $deck = FlashcardDeck::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (! $deck) {
            return response()->json(['error' => 'Deck not found.'], 404);
        }
        $cards = json_decode($deck->cards_json, true);
        if (! is_array($cards)) {
            return response()->json(['error' => 'Invalid deck data.'], 500);
        }

        return response()->json(['topic' => $deck->topic, 'cards' => $cards]);
    }

    public function flashcardsGenerate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['nullable', 'string', 'max:255'],
            'extract' => ['required', 'string'],
        ]);
        $topic = (string) ($data['topic'] ?? 'Topic');
        $out = $this->flashcardGenerator->buildFlashcards($topic, $data['extract']);
        $deck = FlashcardDeck::create([
            'user_id' => $request->user()->id,
            'topic_id' => $this->topics->resolveId($topic),
            'topic' => $topic,
            'cards_json' => json_encode($out['cards']),
        ]);
        $this->discord->sendLearningTip(LearningTipCatalog::random(), $request->user()->id, $request->user()->name);

        return response()->json(['deckId' => $deck->id, 'cards' => $out['cards']]);
    }

    public function tipsSend(Request $request): JsonResponse
    {
        $tip = LearningTipCatalog::random();
        $webhook = $this->discord->sendLearningTip($tip, $request->user()?->id, $request->user()?->name);

        return response()->json([
            'tip' => $tip,
            'discord' => $webhook,
            'webhook_delivered' => $webhook['delivered'] ?? false,
        ]);
    }
}
