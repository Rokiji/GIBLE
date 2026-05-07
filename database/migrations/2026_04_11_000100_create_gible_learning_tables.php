<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->text('summary_snippet')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('study_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->dateTime('remind_at');
            $table->dateTime('notified_at')->nullable();
            $table->timestamps();
            $table->index(['remind_at', 'notified_at']);
        });

        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->unsignedInteger('score');
            $table->unsignedInteger('total');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('flashcard_decks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic');
            $table->longText('cards_json');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flashcard_decks');
        Schema::dropIfExists('quiz_results');
        Schema::dropIfExists('study_reminders');
        Schema::dropIfExists('search_histories');
    }
};
