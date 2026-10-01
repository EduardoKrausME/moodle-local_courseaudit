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
 * Deterministic date consistency checks.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class dates_rule extends base_rule {

    public function run(array $snapshot): array {
        $findings = [];
        $course = $snapshot['course'] ?? [];
        if (!empty($course['startdate']) && !empty($course['enddate']) && $course['enddate'] < $course['startdate']) {
            $findings[] = $this->finding(
                finding::ERROR,
                'dates',
                'finding:coursedates:title',
                'finding:coursedates:description'
            );
        }

        foreach ($snapshot['sections'] ?? [] as $section) {
            foreach ($section['modules'] as $module) {
                $dates = $module['dates'] ?? [];
                $pairs = $this->date_pairs((string)$module['modname']);
                foreach ($pairs as [$fromfield, $tofield]) {
                    $from = (int)($dates[$fromfield] ?? 0);
                    $to = (int)($dates[$tofield] ?? 0);
                    if ($from > 0 && $to > 0 && $to < $from) {
                        $a = (object)[
                            'activity' => $module['name'],
                            'from' => userdate($from),
                            'to' => userdate($to),
                        ];
                        $findings[] = $this->finding(
                            finding::ERROR,
                            'dates',
                            'finding:dateorder:title',
                            'finding:dateorder:description',
                            $a,
                            (string)$module['name'],
                            (string)$module['editurl']
                        );
                    }
                }

                if (!empty($dates['duedate']) && !empty($dates['cutoffdate']) && $dates['cutoffdate'] < $dates['duedate']) {
                    $findings[] = $this->finding(
                        finding::WARNING,
                        'dates',
                        'finding:cutoffbeforeduedate:title',
                        'finding:cutoffbeforeduedate:description',
                        $module['name'],
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }
            }
        }

        return $findings;
    }

    /**
     * Date pairs whose second value must not precede the first.
     *
     * @param string $modname
     * @return array
     */
    private function date_pairs(string $modname): array {
        $pairs = [
            ['timeopen', 'timeclose'],
            ['available', 'deadline'],
            ['submissionstart', 'submissionend'],
            ['assessmentstart', 'assessmentend'],
            ['timeavailablefrom', 'timeavailableto'],
            ['timeviewfrom', 'timeviewto'],
        ];
        if ($modname === 'assign') {
            $pairs[] = ['allowsubmissionsfromdate', 'duedate'];
            $pairs[] = ['allowsubmissionsfromdate', 'cutoffdate'];
        }
        return $pairs;
    }
}
