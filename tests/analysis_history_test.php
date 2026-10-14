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

use advanced_testcase;
use local_courseaudit\analyzer\analysis_repository;

/**
 * Analysis history visibility.
 *
 * @coversNothing
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analysis_history_test extends advanced_testcase {
    /**
     * A course auditor who cannot view a hidden page cannot read its analysis.
     */
    public function test_external_history_rejects_hidden_module(): void {
        global $DB;

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_user();
        $roleid = (int)$DB->get_field('role', 'id', ['shortname' => 'student'], MUST_EXIST);
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $roleid);
        $context = \context_course::instance($course->id);
        role_change_permission($roleid, $context, 'local/courseaudit:audit', CAP_ALLOW);
        $hidden = $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'visible' => 0,
        ]);
        $this->setUser($student);

        $this->assertTrue(has_capability('local/courseaudit:audit', $context));
        $this->assertFalse(get_fast_modinfo($course)->get_cm($hidden->cmid)->uservisible);
        $this->expectException(\invalid_parameter_exception::class);
        \local_courseaudit\external\analysis_history::api($course->id, $hidden->cmid, 10);
    }

    /**
     * Apply the visible module filter before the SQL result limit.
     */
    public function test_history_filters_disallowed_modules_before_limit(): void {
        global $DB;

        $this->resetAfterTest(true);
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        foreach ([101, 102, 103] as $cmid) {
            $DB->insert_record('local_courseaudit_analysis', (object)[
                'courseid' => $course->id,
                'cmid' => $cmid,
                'userid' => $user->id,
                'analysis_type' => 'full',
                'contenthash' => sha1((string)$cmid),
                'resulttext' => 'Analysis of module ' . $cmid,
                'timecreated' => time(),
                'timemodified' => time(),
            ]);
        }

        $records = analysis_repository::list_history($course->id, 0, 1, [101]);
        $this->assertCount(1, $records);
        $this->assertSame(101, (int)reset($records)->cmid);
        $this->assertSame([], analysis_repository::list_history($course->id, 0, 2, []));
        $this->assertSame([], analysis_repository::list_history($course->id, 103, 2, [101]));
    }
}
