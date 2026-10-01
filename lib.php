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

/**
 * Plugin callbacks for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Add the course audit entry to course navigation.
 *
 * @param navigation_node $parentnode
 * @param stdClass $course
 * @param context_course $context
 * @return void
 */
function local_courseaudit_extend_navigation_course(
    navigation_node $parentnode,
    stdClass        $course,
    context_course  $context
): void {
    if ($course->id == SITEID || !has_capability('local/courseaudit:audit', $context)) {
        return;
    }

    $url = new moodle_url('/local/courseaudit/index.php', ['courseid' => $course->id]);
    $parentnode->add(
        get_string('auditcourse', 'local_courseaudit'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'local_courseaudit'
    );
}
