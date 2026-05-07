<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IntegrationHealthCommand extends Command
{
    protected $signature = 'gible:integration-health';

    protected $description = 'Quick sanity check: database, sessions, YouTube and Discord configuration.';

    public function handle(): int
    {
        $ok = true;

        try {
            DB::connection()->getPdo();
            $this->line(' <info>✓</info> Database reachable');
        } catch (\Throwable $e) {
            $ok = false;
            $this->line(' <fg=red>✗</fg=red> Database: '.$e->getMessage());
        }

        $youtube = config('services.youtube.key');
        $this->line($youtube ? ' <info>✓</info> YOUTUBE_API_KEY is set' : ' <fg=yellow>○</fg=yellow> YOUTUBE_API_KEY missing (videos will not load)');

        $hook = config('services.discord.webhook_url');
        $this->line($hook ? ' <info>✓</info> DISCORD_WEBHOOK_URL is set' : ' <fg=yellow>○</fg=yellow> DISCORD_WEBHOOK_URL missing');

        $session = (string) config('session.driver', 'unknown');
        $this->line(' Session driver: '.$session);

        $this->newLine();
        $this->line('For live automation: run <fg=cyan>php artisan schedule:work</> locally, or cron <fg=cyan>schedule:run</> on a server.');

        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
