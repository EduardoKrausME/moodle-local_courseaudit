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
 * Course audit UI.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');

use local_courseaudit\form\audit_form;
use local_courseaudit\audit_manager;
use local_courseaudit\report_view_builder;

$courseid = required_param('courseid', PARAM_INT);
$course = get_course($courseid);

require_login($course);
$context = context_course::instance($courseid);
require_capability('local/courseaudit:audit', $context);

$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_url(new moodle_url('/local/courseaudit/index.php', ['courseid' => $courseid]));
$PAGE->set_title(get_string('auditcourse', 'local_courseaudit'));
$PAGE->set_heading(format_string($course->fullname));

$form = new audit_form(null, ['course' => $course]);
$result = null;
$error = null;

if ($data = $form->get_data()) {
    require_sesskey();

    try {
        $manager = new audit_manager();
        $result = $manager->run(
            $course,
            (int)$USER->id,
            (string)$data->mode,
            (int)$data->sectionid,
            !empty($data->force)
        );
    } catch (Throwable $exception) {
        debugging($exception->getMessage(), DEBUG_DEVELOPER);
        $error = get_string('auditfailed', 'local_courseaudit');
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('auditcourse', 'local_courseaudit'));
echo html_writer::tag('p', get_string('intro', 'local_courseaudit'), ['class' => 'text-muted']);

$form->display();

if ($error !== null) {
    echo $OUTPUT->notification($error, 'error');
}

if ($result !== null) {
    if (!empty($result['cached'])) {
        echo $OUTPUT->notification(get_string('cachedresult', 'local_courseaudit'), 'info');
    }
    if (!empty($result['aierror'])) {
        echo $OUTPUT->notification(get_string('aiunavailable', 'local_courseaudit', $result['aierror']), 'warning');
    }

    $builder = new report_view_builder();
    echo $OUTPUT->render_from_template('local_courseaudit/report', $builder->build($result));
}

echo $OUTPUT->footer();
