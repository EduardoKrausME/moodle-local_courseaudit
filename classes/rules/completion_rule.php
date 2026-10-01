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

/**
 * Deterministic completion checks.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class completion_rule extends base_rule {
    /** @inheritdoc */
    public function run(array $snapshot): array {
        $findings = [];
        $course = $snapshot['course'] ?? [];

        if (empty($course['enablecompletion'])) {
            $findings[] = $this->finding(
                finding::SUGGESTION,
                'completion',
                'finding:completiondisabled:title',
                'finding:completiondisabled:description'
            );
            return $findings;
        }

        if (empty($course['completioncriteriacount'])) {
            $findings[] = $this->finding(
                finding::WARNING,
                'completion',
                'finding:nocoursecriteria:title',
                'finding:nocoursecriteria:description'
            );
        }

        foreach ($snapshot['sections'] ?? [] as $section) {
            foreach ($section['modules'] as $module) {
                if (!empty($module['visible']) && (int)$module['completion'] === 0) {
                    $findings[] = $this->finding(
                        finding::WARNING,
                        'completion',
                        'finding:nocompletion:title',
                        'finding:nocompletion:description',
                        $module['name'],
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }
            }
        }

        return $findings;
    }
}
