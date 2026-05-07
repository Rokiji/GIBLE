<?php

namespace Tests\Unit;

use App\Support\TopicQuerySanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TopicQuerySanitizerTest extends TestCase
{
    #[DataProvider('sanitizeCases')]
    public function test_sanitize_normalizes_expected_inputs(string $input, string $expected): void
    {
        $this->assertSame($expected, TopicQuerySanitizer::sanitize($input));
    }

    public static function sanitizeCases(): array
    {
        return [
            'c_sharp' => ['C#', 'C#'],
            'f_sharp' => ['  F# ', 'F#'],
            'cpp' => ['C++', 'C++'],
            'node_dot' => ['Node.js', 'Node.js'],
            'wiki_url' => ['https://en.wikipedia.org/wiki/Machine_learning', 'Machine learning'],
            'wiki_url_with_underscores' => ['https://en.wikipedia.org/wiki/Quantum_mechanics', 'Quantum mechanics'],
            'hash_topic_text' => ['Python (programming language)#History', 'Python (programming language)#History'],
            'wiki_url_with_fragment' => ['https://en.wikipedia.org/wiki/C%23_(programming_language)#History', 'C# (programming language)'],
            'unicode' => ['École normale', 'École normale'],
        ];
    }

    public function test_truncates_to_three_hundred_characters(): void
    {
        $input = str_repeat('a', 400);
        $out = TopicQuerySanitizer::sanitize($input);
        if (function_exists('mb_strlen')) {
            $this->assertSame(300, mb_strlen($out, 'UTF-8'));
        } else {
            $this->assertSame(300, strlen($out));
        }
    }
}
