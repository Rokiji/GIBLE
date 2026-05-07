<?php

namespace App\Http\Controllers;

use App\Services\DiscordWebhookService;
use App\Services\LearningTipCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardActionsController extends Controller
{
    public function sendLearningTipDiscord(Request $request, DiscordWebhookService $discord): RedirectResponse
    {
        $tip = LearningTipCatalog::random();
        $result = $discord->sendLearningTip($tip, $request->user()->id, $request->user()->name);

        if ($result['delivered'] ?? false) {
            return redirect()->route('dashboard')->with('demo_tip_status', ['ok' => true, 'detail' => 'Learning tip queued to Discord.']);
        }

        return redirect()->route('dashboard')->with('demo_tip_status', [
            'ok' => false,
            'detail' => ($result['error'] ?? null) ?: 'Discord webhook did not accept the message.',
        ]);
    }

    public function dispatchDueStudyReminders(): RedirectResponse
    {
        Artisan::call('gible:send-reminders');

        return redirect()->route('dashboard')->with(
            'demo_reminder_status',
            'Ran the reminder job: Discord messages were sent for any due reminders with a configured webhook.'
        );
    }
}
