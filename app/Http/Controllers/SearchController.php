<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(): View
    {
        return view('search', [
            'title' => 'Topic Search',
            'user' => auth()->user(),
        ]);
    }
}
