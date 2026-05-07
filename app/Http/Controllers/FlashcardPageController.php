<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class FlashcardPageController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        return view('flashcards', [
            'title' => 'Flashcards',
            'user' => $user,
            'decks' => $user->flashDecks()->latest()->limit(20)->get(),
        ]);
    }
}
