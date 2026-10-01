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

namespace local_courseaudit\local;

/**
 * Prepare plain arrays for Mustache without introducing renderables.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_view_builder {
    /** @var array Category display order. */
    private const CATEGORIES = [
        'structure', 'dates', 'completion', 'grading', 'content', 'pedagogy', 'accessibility', 'links', 'recommendations',
    ];

    /**
     * Build template data.
     *
     * @param array $result
     * @return array
     */
    public function build(array $result): array {
        $groups = [];
        foreach (self::CATEGORIES as $category) {
            $groups[$category] = [];
        }

        foreach ($result['findings'] as $finding) {
            $category = array_key_exists($finding->category, $groups) ? $finding->category : 'recommendations';
            $groups[$category][] = $this->finding_for_view($finding);
        }

        $categories = [];
        foreach (self::CATEGORIES as $category) {
            if (!$groups[$category]) {
                continue;
            }
            $categories[] = [
                'name' => get_string('category:' . $category, 'local_courseaudit'),
                'findings' => $groups[$category],
                'count' => count($groups[$category]),
            ];
        }

        $stats = $result['stats'];
        return [
            'hasfindings' => !empty($result['findings']),
            'nofindings' => empty($result['findings']),
            'categories' => $categories,
            'errorcount' => $stats[finding::ERROR] ?? 0,
            'warningcount' => $stats[finding::WARNING] ?? 0,
            'suggestioncount' => $stats[finding::SUGGESTION] ?? 0,
            'aiinsightcount' => $stats[finding::AI_INSIGHT] ?? 0,
        ];
    }

    /**
     * Convert a finding to escaped-by-Mustache view data.
     *
     * @param finding $finding
     * @return array
     */
    private function finding_for_view(finding $finding): array {
        $severityclass = match ($finding->type) {
            finding::ERROR => 'danger',
            finding::WARNING => 'warning',
            finding::SUGGESTION => 'info',
            finding::AI_INSIGHT => 'secondary',
            default => 'secondary',
        };
        $evidence = array_map(static fn(string $text): array => ['text' => $text], $finding->evidence);

        return [
            'severity' => get_string('severity:' . $finding->type, 'local_courseaudit'),
            'severityclass' => $severityclass,
            'title' => $finding->title,
            'description' => $finding->description,
            'origin' => get_string($finding->source === finding::SOURCE_AI ? 'origin:ai' : 'origin:rule', 'local_courseaudit'),
            'related' => $finding->related,
            'hasrelated' => $finding->related !== '',
            'editurl' => $finding->editurl,
            'hasediturl' => $finding->editurl !== '',
            'evidence' => $evidence,
            'hasevidence' => !empty($evidence),
            'suggestion' => $finding->suggestion,
            'hassuggestion' => $finding->suggestion !== '',
        ];
    }
}
