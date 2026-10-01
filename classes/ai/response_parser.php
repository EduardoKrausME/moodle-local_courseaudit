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

namespace local_courseaudit\ai;

use JsonException;
use local_courseaudit\finding;
use local_courseaudit\text_sanitizer;

/**
 * Strict parser for structured AI findings.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class response_parser {
    /** @var array Supported internal categories. */
    private const CATEGORIES = [
        'structure', 'dates', 'completion', 'grading', 'content', 'pedagogy', 'accessibility', 'links', 'recommendations',
    ];

    /**
     * Parse and sanitize model JSON. HTML returned by a model is never rendered.
     *
     * @param string $text
     * @return finding[]
     * @throws JsonException
     */
    public function parse(string $text): array {
        $text = trim($text);
        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/is', $text, $match)) {
            $text = trim($match[1]);
        }

        $data = json_decode($text, true, 64, JSON_THROW_ON_ERROR);
        if (!is_array($data) || !isset($data['findings']) || !is_array($data['findings'])) {
            throw new JsonException('AI response does not contain a findings array.');
        }

        $result = [];
        foreach (array_slice($data['findings'], 0, 50) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $title = text_sanitizer::plain((string)($row['title'] ?? ''), 200);
            $description = text_sanitizer::plain((string)($row['description'] ?? ''), 2000);
            if ($title === '' || $description === '') {
                continue;
            }

            $type = strtolower((string)($row['type'] ?? finding::AI_INSIGHT));
            if ($type === 'error') {
                $type = finding::WARNING;
            }
            if (!in_array($type, [finding::WARNING, finding::SUGGESTION, finding::AI_INSIGHT], true)) {
                $type = finding::AI_INSIGHT;
            }

            $category = strtolower((string)($row['category'] ?? 'pedagogy'));
            $category = match ($category) {
                'evaluation', 'assessment' => 'grading',
                'coherence', 'pedagogical_coherence' => 'pedagogy',
                'recommendation' => 'recommendations',
                default => $category,
            };
            if (!in_array($category, self::CATEGORIES, true)) {
                $category = 'pedagogy';
            }

            $evidence = [];
            if (is_array($row['evidence'] ?? null)) {
                foreach (array_slice($row['evidence'], 0, 8) as $item) {
                    if (is_scalar($item)) {
                        $clean = text_sanitizer::plain((string)$item, 500);
                        if ($clean !== '') {
                            $evidence[] = $clean;
                        }
                    }
                }
            }

            $result[] = new finding(
                $type,
                $category,
                $title,
                $description,
                finding::SOURCE_AI,
                '',
                '',
                $evidence,
                text_sanitizer::plain((string)($row['suggestion'] ?? ''), 1000)
            );
        }

        return $result;
    }
}
