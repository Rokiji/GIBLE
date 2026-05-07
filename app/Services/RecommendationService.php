<?php

namespace App\Services;

use App\Models\User;

class RecommendationService
{
    /** @var list<array{keys: list<string>, topics: list<string>}> */
    private const RELATED_BY_KEYWORD = [
        ['keys' => ['binary', 'tree', 'bst'], 'topics' => ['Tree traversal', 'AVL tree', 'Data structures', 'Binary search algorithm']],
        ['keys' => ['graph', 'dijkstra', 'bfs', 'dfs'], 'topics' => ['Shortest path', 'Topological sort', 'Minimum spanning tree', 'Graph representation']],
        ['keys' => ['database', 'sql', 'relational'], 'topics' => ['Database normalization', 'Indexing', 'Transactions', 'NoSQL overview']],
        ['keys' => ['ai', 'machine learning', 'neural'], 'topics' => ['Supervised learning', 'Gradient descent', 'Natural language processing', 'Computer vision']],
        ['keys' => ['algorithm', 'sort', 'complexity'], 'topics' => ['Big O notation', 'Divide and conquer', 'Dynamic programming', 'Greedy algorithms']],
        ['keys' => ['network', 'tcp', 'http'], 'topics' => ['OSI model', 'DNS', 'TLS', 'REST APIs']],
        ['keys' => ['object-oriented', 'oop', 'inheritance', 'polymorphism'], 'topics' => ['Encapsulation', 'Design patterns', 'SOLID principles', 'UML diagrams']],
        ['keys' => ['recursion', 'recursive'], 'topics' => ['Recurrence relation', 'Master theorem', 'Memoization', 'Backtracking']],
        ['keys' => ['hash', 'hashing'], 'topics' => ['Hash table', 'Collision resolution', 'Cryptographic hash', 'Consistent hashing']],
        ['keys' => ['stack', 'queue', 'heap'], 'topics' => ['Priority queue', 'Deque', 'Trie', 'Linked list']],
        ['keys' => ['operating system', 'scheduler', 'deadlock'], 'topics' => ['Process management', 'Paging', 'Threads', 'Concurrency control']],
        ['keys' => ['memory', 'cache', 'performance'], 'topics' => ['CPU cache', 'Memory hierarchy', 'Profiling', 'Paging']],
        ['keys' => ['encryption', 'cipher', 'authentication'], 'topics' => ['Public-key cryptography', 'Digital signature', 'Symmetric-key algorithm', 'Kerberos']],
        ['keys' => ['compiler', 'parser', 'lexer'], 'topics' => ['Abstract syntax tree', 'LL parser', 'Formal grammar', 'Code generation']],
        ['keys' => ['regex', 'automata', 'finite state'], 'topics' => ['Regular language', 'Deterministic finite automaton', 'Pushdown automaton', 'Turing machine']],
        ['keys' => ['probability', 'statistics', 'distribution'], 'topics' => ['Bayes theorem', 'Hypothesis testing', 'Correlation', 'Variance']],
        ['keys' => ['linear algebra', 'matrix', 'vector'], 'topics' => ['Eigenvalues and eigenvectors', 'Gaussian elimination', 'Linear transformation', 'Dot product']],
        ['keys' => ['javascript', 'react', 'vue'], 'topics' => ['Document Object Model', 'Progressive web app', 'TypeScript', 'Single-page application']],
        ['keys' => ['php', 'laravel'], 'topics' => ['Model–View–Controller', 'Composer', 'Blade', 'PHPUnit']],
        ['keys' => ['oauth', 'jwt', 'api security'], 'topics' => ['OpenID Connect', 'CSRF prevention', 'CORS', 'Rate limiting']],
    ];

    /** @var list<string> */
    private const DEFAULT_RELATED = ['Data structures', 'Algorithms', 'Computer networks', 'Operating systems'];

    public function __construct(
        private WikipediaRelatedService $wikiRelated,
    ) {}

    /**
     * Topic labels from activity heuristics, merged with Wikipedia "related pages" when available.
     *
     * @return list<string>
     */
    public function recommendTopicsForUser(User $user, string $currentTopic = ''): array
    {
        $weights = [];
        $this->addKeywordMatches($weights, $currentTopic, 4);

        foreach ($user->searches()->latest()->limit(12)->get(['topic']) as $row) {
            $this->addKeywordMatches($weights, (string) $row->topic, 2);
        }

        foreach ($user->flashDecks()->latest()->limit(10)->get(['topic']) as $row) {
            $this->addKeywordMatches($weights, (string) $row->topic, 2);
        }

        foreach ($user->quizResults()->latest()->limit(12)->get(['topic', 'score', 'total']) as $row) {
            $ratio = ($row->total ?? 0) > 0 ? ((float) $row->score / (float) $row->total) : 0.0;
            $weight = $ratio < 0.6 ? 3 : 1;
            $this->addKeywordMatches($weights, (string) $row->topic, $weight);
        }

        if ($weights === []) {
            foreach (self::DEFAULT_RELATED as $t) {
                $weights[$t] = 2;
            }
        }

        $seedTitles = array_values(array_unique(array_filter(array_map('trim', [
            $currentTopic,
            (string) ($user->searches()->latest()->first()?->topic ?? ''),
            (string) ($user->quizResults()->latest()->first()?->topic ?? ''),
            (string) ($user->flashDecks()->latest()->first()?->topic ?? ''),
        ]))));
        /** Cap seeds to limit Wikipedia calls per request (responses are cached). */
        $seedTitles = array_slice($seedTitles, 0, 3);

        foreach ($seedTitles as $seed) {
            foreach ($this->wikiRelated->relatedTitles($seed, 12) as $title) {
                $t = trim($title);
                if ($t === '') {
                    continue;
                }
                $weights[$t] = ($weights[$t] ?? 0) + 4;
            }
        }

        arsort($weights);
        $ordered = array_keys($weights);
        $ordered = $this->rotateRecommendations($ordered, $user->id, $currentTopic);

        return array_slice($ordered, 0, 11);
    }

    /**
     * @param  array<string, int>  $weights
     */
    private function addKeywordMatches(array &$weights, string $source, int $baseWeight): void
    {
        $blob = strtolower($source);
        if ($blob === '') {
            return;
        }

        foreach (self::RELATED_BY_KEYWORD as $row) {
            foreach ($row['keys'] as $keyword) {
                if (! str_contains($blob, $keyword)) {
                    continue;
                }
                foreach ($row['topics'] as $topic) {
                    $weights[$topic] = ($weights[$topic] ?? 0) + $baseWeight;
                }
            }
        }
    }

    /**
     * Rotate top candidates daily so recommendations feel fresh.
     *
     * @param  list<string>  $topics
     * @return list<string>
     */
    private function rotateRecommendations(array $topics, int $userId, string $currentTopic): array
    {
        $count = count($topics);
        if ($count < 2) {
            return $topics;
        }

        $seedInput = $userId.'|'.mb_strtolower(trim($currentTopic)).'|'.now()->format('Y-m-d');
        $seed = abs((int) crc32($seedInput));
        $offset = $seed % $count;
        if ($offset === 0) {
            return $topics;
        }

        return array_merge(array_slice($topics, $offset), array_slice($topics, 0, $offset));
    }
}
