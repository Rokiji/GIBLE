<?php

namespace App\Services;

class FlashcardGenerator
{
    private const PROMPT_PATTERNS = [
        'Define: %s',
        'Why does this matter in %s?',
        'Quick recall: what does this imply for %s?',
        'Identify the key idea in %s.',
        'How would you explain this in %s to a beginner?',
    ];

    private const CLOZE_PROMPTS = [
        'Complete the blank for %s:',
        'Fill in the missing term (%s):',
    ];

    /**
     * @return array{cards: list<array{front: string, back: string}>}
     */
    public function buildFlashcards(string $topic, string $extract, int $maxCards = 20): array
    {
        $raw = preg_split('/[.;]\s+/', $extract) ?: [];
        $parts = [];
        $seen = [];
        foreach ($raw as $p) {
            $p = trim($p);
            if (strlen($p) > 25 && strlen($p) < 350) {
                $k = function_exists('mb_strtolower') ? mb_strtolower($p, 'UTF-8') : strtolower($p);
                if (isset($seen[$k])) {
                    continue;
                }
                $seen[$k] = true;
                $parts[] = $p;
            }
        }

        $cards = [];
        $n = min($maxCards, count($parts));
        for ($i = 0; $i < $n; $i++) {
            $back = $parts[$i];
            $mode = $i % 3;
            if ($mode === 0) {
                $front = sprintf(self::PROMPT_PATTERNS[$i % count(self::PROMPT_PATTERNS)], $topic);
            } elseif ($mode === 1) {
                $front = "Concept check ".($i + 1)." for {$topic}";
            } else {
                $front = $this->buildClozePrompt($topic, $back) ?? "Key point ".($i + 1)." about {$topic}";
            }
            $cards[] = ['front' => $front, 'back' => $back];
        }
        if ($cards === []) {
            $cards[] = [
                'front' => "What is {$topic}?",
                'back' => 'Add a Wikipedia summary or refine your search to generate richer flashcards.',
            ];
        }

        return ['cards' => $cards];
    }

    private function buildClozePrompt(string $topic, string $sentence): ?string
    {
        preg_match_all('/\b[A-Za-z][A-Za-z0-9\-]{4,}\b/u', $sentence, $m);
        $tokens = array_values(array_unique($m[0] ?? []));
        if (count($tokens) < 2) {
            return null;
        }
        $target = $tokens[random_int(0, count($tokens) - 1)];
        $blanked = preg_replace('/\b'.preg_quote($target, '/').'\b/u', '____', $sentence, 1);
        if (! is_string($blanked) || ! str_contains($blanked, '____')) {
            return null;
        }

        return sprintf(self::CLOZE_PROMPTS[random_int(0, count(self::CLOZE_PROMPTS) - 1)], $topic).' '.$blanked;
    }
}
