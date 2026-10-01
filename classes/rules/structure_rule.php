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
 * Deterministic course structure checks.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class structure_rule extends base_rule {
    /** @inheritdoc */
    public function run(array $snapshot): array {
        $findings = [];
        $validcmids = array_map('intval', $snapshot['allcmids'] ?? []);

        foreach ($snapshot['sections'] ?? [] as $section) {
            if (empty($section['modules'])) {
                $findings[] = $this->finding(
                    finding::WARNING,
                    'structure',
                    'finding:emptysection:title',
                    'finding:emptysection:description',
                    $section['name'],
                    (string)$section['name'],
                    (string)$section['editurl']
                );
            }

            $broken = $this->broken_availability_cmids((string)$section['availability'], $validcmids);
            if (in_array(-1, $broken, true)) {
                $findings[] = $this->finding(
                    finding::ERROR,
                    'structure',
                    'finding:invalidavailability:title',
                    'finding:invalidavailability:section',
                    $section['name'],
                    (string)$section['name'],
                    (string)$section['editurl']
                );
            } else if ($broken) {
                $findings[] = $this->finding(
                    finding::ERROR,
                    'structure',
                    'finding:brokenavailability:title',
                    'finding:brokenavailability:description',
                    implode(', ', $broken),
                    (string)$section['name'],
                    (string)$section['editurl']
                );
            }

            foreach ($section['modules'] as $module) {
                if (empty($module['visible'])) {
                    $findings[] = $this->finding(
                        finding::SUGGESTION,
                        'structure',
                        'finding:hiddenactivity:title',
                        'finding:hiddenactivity:description',
                        $module['name'],
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }

                $broken = $this->broken_availability_cmids((string)$module['availability'], $validcmids);
                if (in_array(-1, $broken, true)) {
                    $findings[] = $this->finding(
                        finding::ERROR,
                        'structure',
                        'finding:invalidavailability:title',
                        'finding:invalidavailability:activity',
                        $module['name'],
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                } else if ($broken) {
                    $findings[] = $this->finding(
                        finding::ERROR,
                        'structure',
                        'finding:brokenavailability:title',
                        'finding:brokenavailability:description',
                        implode(', ', $broken),
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }
            }
        }

        return $findings;
    }
}
