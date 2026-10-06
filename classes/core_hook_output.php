<?php
// This file is part of Moodle - http://moodle.org/

namespace local_courseaudit;

use context_course;
use core\hook\output\before_footer_html_generation;
use local_courseaudit\analyzer\analysis_availability;

/**
 * Output hooks for activity-level audit controls.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_hook_output {
    /**
     * Inject activity analyzer controls on editable course pages.
     *
     * @param before_footer_html_generation $hook Hook.
     * @return void
     */
    public static function before_footer_html_generation(before_footer_html_generation $hook): void {
        global $OUTPUT, $PAGE, $COURSE, $USER;

        if (empty($COURSE->id) || $COURSE->id < 2 || empty($USER->id) || $USER->id < 2) {
            return;
        }
        if (strpos($PAGE->pagetype, 'course-view-') !== 0 || !$PAGE->user_is_editing()) {
            return;
        }
        if (!$PAGE->get_popup_notification_allowed()) {
            return;
        }

        $context = context_course::instance($COURSE->id);
        if (!has_capability('local/courseaudit:audit', $context)) {
            return;
        }

        $cmids = analysis_availability::get_analyzable_cmids($COURSE, $USER->id);
        if (!$cmids) {
            return;
        }

        $PAGE->requires->strings_for_js([
            'analyzing_activity',
            'analysis_result',
            'analysis_error',
            'analysis_print_popup_blocked',
            'analysis_no_content',
            'analysis_recommendations',
            'analysis_model_warning',
            'analysis_last',
            'analysis_print',
        ], 'local_courseaudit');

        echo $OUTPUT->render_from_template('local_courseaudit/activity_analyzer_modal', [
            'courseid' => (int)$COURSE->id,
        ]);

        $PAGE->requires->js_call_amd('local_courseaudit/activity-analyzer', 'init', [
            (int)$COURSE->id,
            $cmids,
        ]);
    }
}
