<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_courseaudit;

use core_text;

/**
 * Text normalization and redaction utilities.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class text_sanitizer {
    /**
     * Convert arbitrary HTML/text to normalized plain text.
     *
     * @param string|null $value
     * @param int $limit
     * @return string
     */
    public static function plain(?string $value, int $limit = 0): string {
        if ($value === null || $value === '') {
            return '';
        }

        $value = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', ' ', $value) ?? $value;
        $value = strip_tags($value);
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        $value = trim($value);

        if ($limit > 0 && self::strlen($value) > $limit) {
            $value = self::substr($value, 0, $limit) . '…';
        }

        return $value;
    }

    /**
     * Normalize content for hashing and duplicate detection.
     *
     * @param string|null $value
     * @return string
     */
    public static function normalize(?string $value): string {
        $value = self::plain($value);
        $value = core_text::strtolower($value);
        $value = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return trim($value);
    }

    /**
     * Redact common personal identifiers and secrets before AI use.
     *
     * @param string|null $value
     * @param int $limit
     * @return string
     */
    public static function for_ai(?string $value, int $limit = 4000): string {
        $value = self::plain($value);
        $patterns = [
            '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/iu' => '[email removed]',
            '/\bBearer\s+[A-Za-z0-9._~+\/-]+=*/i' => '[token removed]',
            '/\b(api[_-]?key|access[_-]?token|secret|password)\s*[:=]\s*[^\s,;]+/iu' => '$1=[removed]',
        ];
        foreach ($patterns as $pattern => $replacement) {
            $value = preg_replace($pattern, $replacement, $value) ?? $value;
        }

        if ($limit > 0 && self::strlen($value) > $limit) {
            $value = self::substr($value, 0, $limit) . '…';
        }
        return $value;
    }

    /**
     * Method strlen.
     *
     * @param string $text Parameter text.
     * @return int Return value.
     */
    private static function strlen(string $text): int {
        return class_exists('core_text') ? core_text::strlen($text) : mb_strlen($text);
    }

    /**
     * Method substr.
     *
     * @param string $text Parameter text.
     * @param int $start Parameter start.
     * @param int $length Parameter length.
     * @return string Return value.
     */
    private static function substr(string $text, int $start, int $length): string {
        return class_exists('core_text') ? core_text::substr($text, $start, $length) : mb_substr($text, $start, $length);
    }
}
