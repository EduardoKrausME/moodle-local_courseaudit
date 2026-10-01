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

/**
 * Build the minimal structured context sent to AI.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class context_builder {
    /**
     * Maximum total JSON payload size before stopping at additional activities.
     */
    private const MAX_TOTAL_CHARS = 60000;

    /**
     * Build AI-safe course context.
     *
     * @param array $snapshot
     * @return array
     */
    public function build(array $snapshot): array {
        $course = $snapshot['course'] ?? [];
        $payload = [
            'course' => [
                'title' => text_sanitizer::for_ai((string)($course['fullname'] ?? ''), 500),
                'summary' => text_sanitizer::for_ai((string)($course['summary'] ?? ''), 5000),
            ],
            'sections' => [],
        ];

        $budget = self::MAX_TOTAL_CHARS;
        $budgetexhausted = false;
        foreach ($snapshot['sections'] ?? [] as $section) {
            $sectionrow = [
                'name' => text_sanitizer::for_ai((string)$section['name'], 500),
                'summary' => text_sanitizer::for_ai((string)$section['summary'], 3000),
                'activity_count' => count($section['modules']),
                'activities' => [],
            ];
            foreach ($section['modules'] as $module) {
                $row = [
                    'type' => (string)$module['modname'],
                    'name' => text_sanitizer::for_ai((string)$module['name'], 500),
                    'text' => text_sanitizer::for_ai((string)$module['text'], 4000),
                    'word_count' => $this->word_count(text_sanitizer::plain((string)$module['text'])),
                    'graded' => ($module['grade'] ?? null) !== null && (float)$module['grade'] != 0.0,
                    'completion_tracked' => (int)($module['completion'] ?? 0) !== 0,
                ];
                $encoded = json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $cost = strlen($encoded === false ? '' : $encoded);
                if ($cost > $budget) {
                    $budgetexhausted = true;
                    break;
                }
                $budget -= $cost;
                $sectionrow['activities'][] = $row;
            }
            $payload['sections'][] = $sectionrow;
            if ($budgetexhausted) {
                break;
            }
        }

        return $payload;
    }

    /**
     * Count words without losing accented and other Unicode letters.
     *
     * @param string $text
     * @return int
     */
    private function word_count(string $text): int {
        if ($text === '') {
            return 0;
        }
        preg_match_all('/[\p{L}\p{N}]+(?:[’\'\-][\p{L}\p{N}]+)*/u', $text, $matches);
        return count($matches[0] ?? []);
    }
}
