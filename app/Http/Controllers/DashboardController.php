<?php

namespace App\Http\Controllers;

use App\Services\IntegrationService;
use App\Services\RecommendationService;
use App\Models\WebhookDelivery;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private RecommendationService $recommendations,
        private IntegrationService $integration,
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $user->loadCount(['searches', 'reminders', 'quizResults', 'flashDecks']);

        [$recentSearches, $reminders, $quizResults, $decks] = [
            $user->searches()->latest()->limit(8)->get(),
            $user->reminders()->orderBy('remind_at')->limit(10)->get(),
            $user->quizResults()->latest()->limit(6)->get(),
            $user->flashDecks()->latest()->limit(4)->get(),
        ];

        $recTopics = $this->recommendations->recommendTopicsForUser($user);
        $recommendedMaterials = [];
        foreach (array_slice($recTopics, 0, 3) as $label) {
            $recommendedMaterials[] = $this->integration->enrichRecommendationTopic($label);
        }

        $since = now()->subDays(7);
        $activityTrend = [
            'searches' => $user->searches()->where('created_at', '>=', $since)->count(),
            'quizzes' => $user->quizResults()->where('created_at', '>=', $since)->count(),
            'decks' => $user->flashDecks()->where('created_at', '>=', $since)->count(),
        ];
        $avgQuizPercent = round((float) $user->quizResults()
            ->where('total', '>', 0)
            ->selectRaw('AVG((score * 100.0) / total) as avg_percent')
            ->value('avg_percent') ?: 0, 1);

        $lowScoreTopics = $user->quizResults()
            ->selectRaw('topic, AVG((score * 1.0) / NULLIF(total, 0)) as ratio')
            ->where('total', '>', 0)
            ->groupBy('topic')
            ->orderBy('ratio')
            ->limit(4)
            ->get()
            ->map(fn ($row) => [
                'topic' => $row->topic,
                'ratio' => round(((float) $row->ratio) * 100, 1),
            ])
            ->all();

        $webhookStats = $user->webhookDeliveries()
            ->where('service', 'discord')
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN delivered = 1 THEN 1 ELSE 0 END) as delivered')
            ->first();
        $webhookHealth = [
            'total' => (int) ($webhookStats->total ?? 0),
            'delivered' => (int) ($webhookStats->delivered ?? 0),
        ];
        $dueRemindersCount = $user->reminders()
            ->where('remind_at', '<=', now())
            ->whereNull('notified_at')
            ->count();

        $latestTip = WebhookDelivery::query()
            ->where('user_id', $user->id)
            ->where('service', 'discord')
            ->where('event_type', 'learning_tip')
            ->latest('created_at')
            ->first();

        return view('dashboard', [
            'title' => 'Smart Study Dashboard',
            'user' => $user,
            'recentSearches' => $recentSearches,
            'reminders' => $reminders,
            'quizResults' => $quizResults,
            'decks' => $decks,
            'recommendedMaterials' => $recommendedMaterials,
            'extraRecommendations' => array_slice($recTopics, 3, 4),
            'activityTrend' => $activityTrend,
            'avgQuizPercent' => $avgQuizPercent,
            'lowScoreTopics' => $lowScoreTopics,
            'webhookHealth' => $webhookHealth,
            'dueRemindersCount' => $dueRemindersCount,
            'latestTip' => $latestTip,
        ]);
    }
}
