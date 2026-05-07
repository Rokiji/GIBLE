<?php

use App\Http\Controllers\Api\LearningApiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardActionsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FlashcardPageController;
use App\Http\Controllers\PlannerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizPageController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/dashboard/demo/tip-discord', [DashboardActionsController::class, 'sendLearningTipDiscord'])
        ->middleware('throttle:8,1')
        ->name('dashboard.demo.tip-discord');
    Route::post('/dashboard/demo/run-reminders', [DashboardActionsController::class, 'dispatchDueStudyReminders'])
        ->middleware('throttle:4,1')
        ->name('dashboard.demo.run-reminders');

    Route::get('/search', SearchController::class)->name('search');
    Route::get('/quiz', QuizPageController::class)->name('quiz');
    Route::get('/flashcards', FlashcardPageController::class)->name('flashcards');
    Route::get('/planner', [PlannerController::class, 'show'])->name('planner');
    Route::post('/planner', [PlannerController::class, 'store']);

    Route::prefix('api')->group(function () {
        Route::get('/search', [LearningApiController::class, 'search']);
        Route::post('/quiz/submit', [LearningApiController::class, 'quizSubmit']);
        Route::post('/quiz/generate', [LearningApiController::class, 'quizGenerate']);
        Route::get('/flashcards/deck/{id}', [LearningApiController::class, 'flashcardsDeck'])->whereNumber('id');
        Route::post('/flashcards/generate', [LearningApiController::class, 'flashcardsGenerate']);
        Route::post('/tips/send', [LearningApiController::class, 'tipsSend']);
    });
});
