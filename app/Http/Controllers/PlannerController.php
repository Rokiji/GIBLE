<?php

namespace App\Http\Controllers;

use App\Services\DiscordWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlannerController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        return view('planner', [
            'title' => 'Study Planner',
            'user' => $user,
            'reminders' => $user->reminders()->orderBy('remind_at')->get(),
        ]);
    }

    public function store(Request $request, DiscordWebhookService $discord): RedirectResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'remind_at' => ['required', 'date'],
        ]);

        $user = $request->user();
        $reminder = $user->reminders()->create([
            'subject' => trim($data['subject']),
            'remind_at' => $data['remind_at'],
        ]);

        $webhook = $discord->sendStudyReminder($reminder->subject, $reminder->remind_at, $user->id, $user->name);

        if ($webhook['delivered'] ?? false) {
            return redirect()->route('planner')->with('status', 'Reminder saved and sent to Discord.');
        }

        return redirect()->route('planner')->with('status', 'Reminder saved. Discord send was skipped or failed.');
    }
}
