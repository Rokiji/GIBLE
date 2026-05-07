<?php

namespace App\Support;

/**
 * Normalizes learner topic input for outbound APIs — does not mutate display beyond trimming/safety.
 */
class TopicQuerySanitizer
{
    public static function sanitize(string $topic): string
    {
        $t = trim($topic);
        if ($t === '') {
            return '';
        }

        $t = str_replace("\0", '', $t);

        if (extension_loaded('intl') && class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($t, \Normalizer::FORM_C);
            if (is_string($normalized)) {
                $t = $normalized;
            }
        }

        $t = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $t) ?? '';

        if (preg_match('#^https?://#i', $t)) {
            $parts = parse_url($t);
            if (is_array($parts) && isset($parts['path']) && $parts['path'] !== '') {
                $path = $parts['path'];
                if (preg_match('#/wiki/(.+)$#u', $path, $m)) {
                    $t = rawurldecode(str_replace('_', ' ', $m[1]));
                }
            }
        }

        $t = preg_replace('/\s+/u', ' ', $t) ?? '';
        $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $t) ?? '';

        if (function_exists('mb_strlen') && mb_strlen($t, 'UTF-8') > 300) {
            $t = mb_substr($t, 0, 300, 'UTF-8');
        } elseif (\strlen($t) > 300) {
            $t = substr($t, 0, 300);
        }

        return trim($t);
    }
}
