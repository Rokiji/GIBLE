<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['search_histories', 'quiz_results', 'flashcard_decks'];
        foreach ($tables as $table) {
            DB::table($table)
                ->select('id', 'topic')
                ->whereNull('topic_id')
                ->orderBy('id')
                ->chunkById(200, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        $topic = trim((string) $row->topic);
                        if ($topic === '') {
                            continue;
                        }
                        $slug = Str::of($topic)->lower()->slug(' ')->replace(' ', '-')->value();
                        if ($slug === '') {
                            continue;
                        }
                        $existing = DB::table('learning_topics')->where('slug', $slug)->first();
                        if (! $existing) {
                            $id = DB::table('learning_topics')->insertGetId([
                                'slug' => $slug,
                                'label' => Str::title($topic),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } else {
                            $id = $existing->id;
                        }
                        DB::table($table)->where('id', $row->id)->update(['topic_id' => $id]);
                    }
                });
        }
    }

    public function down(): void
    {
        DB::table('search_histories')->update(['topic_id' => null]);
        DB::table('quiz_results')->update(['topic_id' => null]);
        DB::table('flashcard_decks')->update(['topic_id' => null]);
    }
};
