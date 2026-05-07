<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\DiscordWebhookService;
use App\Services\LearningTipCatalog;
use Illuminate\Console\Command;

class SendLearningTips extends Command
{
    protected $signature = 'gible:send-learning-tips {--limit=25 : Max users to notify}';

    protected $description = 'Send daily learning tips via Discord webhook to active learners.';

    public function handle(DiscordWebhookService $discord): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $users = User::query()
            ->where(function ($q) {
                $q->whereHas('searches', fn ($sq) => $sq->where('created_at', '>=', now()->subDays(14)))
                    ->orWhereHas('quizResults', fn ($qq) => $qq->where('created_at', '>=', now()->subDays(14)))
                    ->orWhereHas('flashDecks', fn ($fq) => $fq->where('created_at', '>=', now()->subDays(14)));
            })
            ->whereDoesntHave('webhookDeliveries', function ($q) {
                $q->where('service', 'discord')
                    ->where('event_type', 'learning_tip')
                    ->whereDate('created_at', today());
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        foreach ($users as $user) {
            $discord->sendLearningTip(LearningTipCatalog::random(), $user->id);
        }

        $this->info("Sent learning tips to {$users->count()} active users.");

        return self::SUCCESS;
    }
}
