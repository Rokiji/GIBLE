<?php

namespace App\Console\Commands;

use App\Models\StudyReminder;
use App\Services\DiscordWebhookService;
use Illuminate\Console\Command;

class SendDueStudyReminders extends Command
{
    protected $signature = 'gible:send-reminders';

    protected $description = 'Send due study reminders via Discord webhook and mark them notified.';

    public function handle(DiscordWebhookService $discord): int
    {
        $now = now();
        $due = StudyReminder::query()
            ->where('remind_at', '<=', $now)
            ->whereNull('notified_at')
            ->with('user')
            ->limit(50)
            ->get();

        foreach ($due as $r) {
            $result = $discord->sendStudyReminder($r->subject, $r->remind_at, $r->user_id, $r->user?->name);
            if ($result['ok'] ?? false) {
                $r->update(['notified_at' => now()]);
            }
        }

        return self::SUCCESS;
    }
}
