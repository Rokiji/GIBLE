<?php

namespace App\Services;

class QuizGenerator
{
    private const DISTRACTORS = [
        'It is primarily a hardware specification for printers.',
        'It was deprecated in favor of analog signal processing only.',
        'It applies exclusively to marine navigation charts.',
        'It is unrelated to computation and only used in music theory.',
        'It refers only to spreadsheet formatting conventions.',
    ];

    private const STEMS = [
        'Which statement about "%s" is directly supported by the overview?',
        'Based on the summary, which option best describes "%s"?',
        'Choose the idea that most accurately matches the explanation of "%s".',
        'From the reading, which statement is true about "%s"?',
        'Which choice aligns with the key point presented for "%s"?',
    ];

    private const KEYWORD_PROMPTS = [
        'Which keyword appears in the overview context for "%s"?',
        'Which term is most relevant to the summary of "%s"?',
    ];

    private const CLOZE_PROMPTS = [
        'Fill in the blank based on the summary for "%s":',
        'Which option best completes this statement about "%s"?',
    ];

    private const APPLY_PROMPTS = [
        'A student needs to explain "%s" in one sentence. Which option is most accurate?',
        'In a quick review of "%s", which statement should be chosen?',
    ];

    /**
     * @return list<string>
     */
    private function sentencesFromExtract(string $extract): array
    {
        $parts = preg_split('/(?<=[.!?])\s+/', $extract) ?: [];
        $out = [];
        foreach ($parts as $s) {
            $s = trim($s);
            if (strlen($s) > 30 && strlen($s) < 400) {
                $out[] = $s;
            }
        }

        return $out;
    }

    /**
     * @param  list<string>  $arr
     * @return list<string>
     */
    private function shuffle(array $arr): array
    {
        $a = $arr;
        for ($i = count($a) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$a[$i], $a[$j]] = [$a[$j], $a[$i]];
        }

        return $a;
    }

    /**
     * @return list<string>
     */
    private function keywordsFromExtract(string $extract): array
    {
        preg_match_all('/\b[A-Za-z][A-Za-z0-9\-]{4,}\b/u', $extract, $matches);
        $tokens = array_map('strtolower', $matches[0] ?? []);
        $stop = [
            'which', 'their', 'there', 'about', 'these', 'those', 'where', 'while', 'being', 'through',
            'after', 'before', 'under', 'using', 'used', 'when', 'from', 'into', 'than', 'with', 'without',
            'because', 'between', 'within', 'across', 'overview', 'summary', 'topic', 'system', 'learning',
        ];
        $freq = [];
        foreach ($tokens as $t) {
            if (in_array($t, $stop, true)) {
                continue;
            }
            $freq[$t] = ($freq[$t] ?? 0) + 1;
        }
        arsort($freq);

        return array_slice(array_keys($freq), 0, 12);
    }

    private function randomFrom(array $items): string
    {
        return $items[random_int(0, count($items) - 1)];
    }

    private function normalize(string $text): string
    {
        $lower = function_exists('mb_strtolower') ? mb_strtolower($text, 'UTF-8') : strtolower($text);

        return preg_replace('/\s+/u', ' ', trim($lower)) ?? '';
    }

    private function uniqueSentencePool(array $sentences): array
    {
        $seen = [];
        $out = [];
        foreach ($sentences as $s) {
            $k = $this->normalize($s);
            if ($k === '' || isset($seen[$k])) {
                continue;
            }
            $seen[$k] = true;
            $out[] = $s;
        }

        return $out;
    }

    /**
     * @param  list<string>  $keywords
     */
    private function buildKeywordQuestion(string $topic, array $keywords, array &$usedQuestions, array &$usedAnswers): ?array
    {
        if (count($keywords) < 4) {
            return null;
        }
        $correct = $keywords[random_int(0, count($keywords) - 1)];
        if (isset($usedAnswers['kw:'.$correct])) {
            return null;
        }
        $wrongPool = array_values(array_filter($keywords, fn ($k) => $k !== $correct));
        $wrong = array_slice($this->shuffle($wrongPool), 0, 3);
        if (count($wrong) < 3) {
            return null;
        }
        $question = sprintf($this->randomFrom(self::KEYWORD_PROMPTS), $topic);
        if (isset($usedQuestions[$question])) {
            return null;
        }
        $usedQuestions[$question] = true;
        $usedAnswers['kw:'.$correct] = true;
        $options = $this->shuffle(array_map('ucfirst', array_merge([$correct], $wrong)));

        return ['question' => $question, 'options' => $options, 'answer' => ucfirst($correct)];
    }

    /**
     * @param  list<string>  $sentences
     */
    private function buildClozeQuestion(string $topic, array $sentences, array &$usedQuestions, array &$usedAnswers): ?array
    {
        $candidates = array_values(array_filter($sentences, function ($s) {
            preg_match_all('/\b[A-Za-z][A-Za-z0-9\-]{4,}\b/u', $s, $m);

            return count($m[0] ?? []) >= 2;
        }));
        if ($candidates === []) {
            return null;
        }
        $base = $candidates[random_int(0, count($candidates) - 1)];
        preg_match_all('/\b[A-Za-z][A-Za-z0-9\-]{4,}\b/u', $base, $m);
        $tokens = array_values(array_unique(array_map('strtolower', $m[0] ?? [])));
        if (count($tokens) < 2) {
            return null;
        }
        $correct = $tokens[random_int(0, count($tokens) - 1)];
        if (isset($usedAnswers['cl:'.$correct])) {
            return null;
        }
        $blanked = preg_replace('/\b'.preg_quote($correct, '/').'\b/iu', '____', $base, 1);
        if (! is_string($blanked) || ! str_contains($blanked, '____')) {
            return null;
        }
        $wrongPool = array_values(array_filter($tokens, fn ($t) => $t !== $correct));
        if (count($wrongPool) < 3) {
            return null;
        }
        $question = sprintf($this->randomFrom(self::CLOZE_PROMPTS), $topic).' '.$blanked;
        if (isset($usedQuestions[$question])) {
            return null;
        }
        $usedQuestions[$question] = true;
        $usedAnswers['cl:'.$correct] = true;
        $options = $this->shuffle(array_map('ucfirst', array_merge([$correct], array_slice($this->shuffle($wrongPool), 0, 3))));

        return ['question' => $question, 'options' => $options, 'answer' => ucfirst($correct)];
    }

    /**
     * @param  list<string>  $sentences
     */
    private function buildApplyQuestion(string $topic, array $sentences, array &$usedQuestions, array &$usedAnswers): ?array
    {
        if ($sentences === []) {
            return null;
        }
        $correct = $sentences[random_int(0, count($sentences) - 1)];
        if (isset($usedAnswers['ap:'.$correct])) {
            return null;
        }
        $wrongPool = array_values(array_filter(
            array_merge(self::DISTRACTORS, array_filter($sentences, fn ($x) => $x !== $correct)),
            fn ($x) => $x !== $correct
        ));
        $wrong = array_slice($this->shuffle($wrongPool), 0, 3);
        if (count($wrong) < 3) {
            return null;
        }
        $question = sprintf($this->randomFrom(self::APPLY_PROMPTS), $topic);
        if (isset($usedQuestions[$question])) {
            return null;
        }
        $usedQuestions[$question] = true;
        $usedAnswers['ap:'.$correct] = true;
        $options = $this->shuffle(array_merge([$correct], $wrong));

        return ['question' => $question, 'options' => $options, 'answer' => $correct];
    }

    /**
     * @return array{questions: list<array{question: string, options: list<string>, answer: string}>}
     */
    public function buildQuiz(string $topic, string $extract, int $count = 10): array
    {
        $sents = $this->uniqueSentencePool($this->sentencesFromExtract($extract));
        $keywords = $this->keywordsFromExtract($extract);
        if ($sents === []) {
            return [
                'questions' => [[
                    'question' => "Which area best matches \"{$topic}\" as a learning focus?",
                    'options' => $this->shuffle(array_merge(['Computer science / IT'], array_slice(self::DISTRACTORS, 0, 3))),
                    'answer' => 'Computer science / IT',
                ]],
            ];
        }

        $questions = [];
        $usedQuestions = [];
        $usedAnswers = [];
        $patterns = ['st', 'kw', 'cl', 'ap'];
        $tries = 0;
        while (count($questions) < $count && $tries < 160) {
            $tries++;
            $mode = $patterns[$tries % count($patterns)];
            $built = null;
            if ($mode === 'kw') {
                $built = $this->buildKeywordQuestion($topic, $keywords, $usedQuestions, $usedAnswers);
            } elseif ($mode === 'cl') {
                $built = $this->buildClozeQuestion($topic, $sents, $usedQuestions, $usedAnswers);
            } elseif ($mode === 'ap') {
                $built = $this->buildApplyQuestion($topic, $sents, $usedQuestions, $usedAnswers);
            } else {
                $built = $this->buildApplyQuestion($topic, $sents, $usedQuestions, $usedAnswers);
                if ($built !== null) {
                    $built['question'] = sprintf($this->randomFrom(self::STEMS), $topic);
                }
            }
            if ($built !== null) {
                $questions[] = $built;
            }
        }

        if ($questions === []) {
            $questions[] = [
                'question' => sprintf($this->randomFrom(self::STEMS), $topic),
                'options' => $this->shuffle(array_merge([$sents[0]], array_slice(self::DISTRACTORS, 0, 3))),
                'answer' => $sents[0],
            ];
        }

        return ['questions' => $questions];
    }
}
