<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class QuizPageController extends Controller
{
    public function __invoke(): View
    {
        return view('quiz', [
            'title' => 'Quiz Generator',
            'user' => auth()->user(),
        ]);
    }
}
