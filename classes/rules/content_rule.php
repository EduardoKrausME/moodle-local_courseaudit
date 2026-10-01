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

namespace local_courseaudit\rules;

use local_courseaudit\local\finding;
use local_courseaudit\local\text_sanitizer;

/**
 * Deterministic content checks.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_rule extends base_rule {
    /** @inheritdoc */
    public function run(array $snapshot): array {
        $findings = [];
        $hashes = [];

        foreach ($snapshot['sections'] ?? [] as $section) {
            foreach ($section['modules'] as $module) {
                $text = trim((string)$module['text']);
                if ($this->expects_description((string)$module['modname']) && text_sanitizer::plain($module['introhtml']) === '') {
                    $findings[] = $this->finding(
                        finding::SUGGESTION,
                        'content',
                        'finding:nodescription:title',
                        'finding:nodescription:description',
                        $module['name'],
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }

                $normalized = text_sanitizer::normalize($text);
                if (mb_strlen($normalized) >= 80) {
                    $hash = hash('sha256', $normalized);
                    $hashes[$hash][] = $module;
                }
            }
        }

        foreach ($hashes as $modules) {
            if (count($modules) < 2) {
                continue;
            }
            $names = array_map(static fn(array $module): string => (string)$module['name'], $modules);
            $findings[] = $this->finding(
                finding::WARNING,
                'content',
                'finding:duplicatecontent:title',
                'finding:duplicatecontent:description',
                implode(', ', $names),
                implode(' / ', $names),
                (string)$modules[0]['editurl']
            );
        }

        return $findings;
    }

    /**
     * Resource types for which an empty intro is worth reviewing.
     *
     * @param string $modname
     * @return bool
     */
    private function expects_description(string $modname): bool {
        return in_array($modname, [
            'assign', 'book', 'choice', 'feedback', 'forum', 'glossary', 'lesson', 'page', 'quiz', 'resource', 'url',
            'workshop',
        ], true);
    }
}
