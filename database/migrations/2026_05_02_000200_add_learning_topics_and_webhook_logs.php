<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('label')->unique();
            $table->timestamps();
        });

        Schema::table('search_histories', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->nullable()
                ->after('user_id')
                ->constrained('learning_topics')
                ->nullOnDelete();
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->nullable()
                ->after('user_id')
                ->constrained('learning_topics')
                ->nullOnDelete();
        });

        Schema::table('flashcard_decks', function (Blueprint $table) {
            $table->foreignId('topic_id')
                ->nullable()
                ->after('user_id')
                ->constrained('learning_topics')
                ->nullOnDelete();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service', 80);
            $table->string('event_type', 120);
            $table->boolean('delivered')->default(false);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_message')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['service', 'event_type', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');

        Schema::table('flashcard_decks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        Schema::table('search_histories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('topic_id');
        });

        Schema::dropIfExists('learning_topics');
    }
};
