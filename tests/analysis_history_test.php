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
 * @covers \local_courseaudit\analyzer\analysis_repository
 * @package local_courseaudit
 */
final class analysis_history_test extends advanced_testcase {
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
