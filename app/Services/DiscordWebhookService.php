<?php

namespace App\Services;

use App\Models\User;
use App\Models\WebhookDelivery;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class DiscordWebhookService
{
    /**
     * @param  list<array>|null  $embeds
     * @return array{ok: bool, status?: int, error?: string, delivered: bool}
     */
    public function sendMessage(
        string $content,
        ?array $embeds = null,
        string $eventType = 'generic',
        ?int $userId = null,
        array $meta = []
    ): array
    {
        $maxAttempts = max(1, (int) config('services.discord.max_attempts', 2));
        $url = config('services.discord.webhook_url');
        if (! $url) {
            return $this->persistAndReturn(
                userId: $userId,
                eventType: $eventType,
                attempts: 0,
                delivered: false,
                status: null,
                error: 'DISCORD_WEBHOOK_URL is not set.',
                meta: $meta
            );
        }

        $attempts = 0;
        $lastError = null;
        $lastStatus = null;
        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                $response = Http::timeout(10)->post($url, [
                    'content' => mb_substr($content, 0, 2000),
                    'embeds' => $embeds ?: null,
                ]);
                $lastStatus = $response->status();
                if ($response->successful()) {
                    return $this->persistAndReturn(
                        userId: $userId,
                        eventType: $eventType,
                        attempts: $attempts,
                        delivered: true,
                        status: $lastStatus,
                        error: null,
                        meta: $meta
                    );
                }
                $lastError = 'Discord webhook returned HTTP '.$lastStatus.'.';
            } catch (\Throwable $e) {
                $lastError = $e->getMessage() ?: 'Webhook request failed.';
            }

            if ($attempts < $maxAttempts) {
                usleep(250000 * $attempts);
            }
        }

        return $this->persistAndReturn(
            userId: $userId,
            eventType: $eventType,
            attempts: $attempts,
            delivered: false,
            status: $lastStatus,
            error: $lastError ?: 'Webhook request failed.',
            meta: $meta
        );
    }

    public function sendStudyReminder(string $subject, mixed $remindAt, ?int $userId = null, ?string $username = null): array
    {
        $dt = Carbon::parse($remindAt)->timezone(config('app.timezone'));
        $when = $dt->isoFormat('MMMM D, YYYY [at] h:mm A');
        $by = $this->resolveUserLabel($userId, $username);

        return $this->sendMessage(
            "Reminder created by **{$by}**: Study **{$subject}** on {$when}.",
            null,
            'study_reminder',
            $userId,
            [
                'subject' => $subject,
                'remind_at' => $dt->toIso8601String(),
                'username' => $by,
                'formatted_time' => $when,
            ]
        );
    }

    public function sendQuizAchievement(string $topic, int $score, int $total, ?int $userId = null): array
    {
        return $this->sendMessage(
            "Quiz achievement on **{$topic}**: {$score}/{$total}. Keep learning with GIBLE.",
            null,
            'quiz_achievement',
            $userId,
            ['topic' => $topic, 'score' => $score, 'total' => $total]
        );
    }

    public function sendLearningTip(string $tip, ?int $userId = null, ?string $username = null): array
    {
        $by = $this->resolveUserLabel($userId, $username);

        return $this->sendMessage(
            "Learning tip for **{$by}**: {$tip}",
            null,
            'learning_tip',
            $userId,
            ['tip' => $tip, 'username' => $by]
        );
    }

    private function resolveUserLabel(?int $userId, ?string $username = null): string
    {
        $name = trim((string) $username);
        if ($name !== '') {
            return $name;
        }

        if ($userId !== null) {
            $dbName = trim((string) (User::query()->whereKey($userId)->value('name') ?? ''));
            if ($dbName !== '') {
                return $dbName;
            }
        }

        return 'Learner';
    }

    /**
     * @return array{ok: bool, delivered: bool, status?: int, error?: string}
     */
    private function persistAndReturn(
        ?int $userId,
        string $eventType,
        int $attempts,
        bool $delivered,
        ?int $status,
        ?string $error,
        array $meta = []
    ): array {
        WebhookDelivery::create([
            'user_id' => $userId,
            'service' => 'discord',
            'event_type' => $eventType,
            'delivered' => $delivered,
            'attempts' => $attempts,
            'http_status' => $status,
            'error_message' => $error,
            'delivered_at' => $delivered ? now() : null,
            'meta' => $meta,
        ]);

        $result = ['ok' => $delivered, 'delivered' => $delivered];
        if ($status) {
            $result['status'] = $status;
        }
        if ($error) {
            $result['error'] = $error;
        }

        return $result;
    }
}
