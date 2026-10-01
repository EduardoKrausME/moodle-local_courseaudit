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

use cm_info;
use moodle_url;
use stdClass;
use xmldb_table;

/**
 * Build a deterministic, user-data-free snapshot of a course.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class snapshot_builder {
    /**
     * Build course snapshot.
     *
     * @param stdClass $course
     * @param int $sectionid 0 for all sections.
     * @return array
     */
    public function build(stdClass $course, int $sectionid = 0): array {
        global $DB;

        $modinfo = get_fast_modinfo($course);
        $sections = [];
        $allcmids = array_map('intval', array_keys($modinfo->get_cms()));
        $completioncriteriacount = $DB->count_records('course_completion_criteria', ['course' => $course->id]);

        foreach ($modinfo->get_section_info_all() as $section) {
            if ($sectionid > 0 && (int)$section->id !== $sectionid) {
                continue;
            }

            $modules = [];
            $cmids = $modinfo->sections[(int)$section->section] ?? [];
            foreach ($cmids as $cmid) {
                $cm = $modinfo->get_cm($cmid);
                $modules[] = $this->build_module($course, $cm);
            }

            $sections[] = [
                'id' => (int)$section->id,
                'number' => (int)$section->section,
                'name' => text_sanitizer::plain(get_section_name($course, $section), 500),
                'summary' => (string)$section->summary,
                'visible' => !empty($section->visible),
                'availability' => (string)($section->availability ?? ''),
                'editurl' => (new moodle_url('/course/editsection.php', ['id' => $section->id, 'sr' => $section->section]))->out(false),
                'modules' => $modules,
            ];
        }

        return [
            'course' => [
                'id' => (int)$course->id,
                'fullname' => text_sanitizer::plain($course->fullname, 500),
                'summary' => (string)$course->summary,
                'startdate' => (int)$course->startdate,
                'enddate' => (int)$course->enddate,
                'enablecompletion' => !empty($course->enablecompletion),
                'completioncriteriacount' => $completioncriteriacount,
            ],
            'allcmids' => $allcmids,
            'sections' => $sections,
        ];
    }

    /**
     * Build a module snapshot.
     *
     * @param stdClass $course
     * @param cm_info $cm
     * @return array
     */
    private function build_module(stdClass $course, cm_info $cm): array {
        global $DB;

        $record = $this->get_instance_record($cm);
        $intro = (string)($record->intro ?? '');
        $contentparts = [$intro];

        if (isset($record->content)) {
            $contentparts[] = (string)$record->content;
        }

        if ($cm->modname === 'book' && $DB->get_manager()->table_exists(new xmldb_table('book_chapters'))) {
            $chapters = $DB->get_records('book_chapters', ['bookid' => $cm->instance], 'pagenum ASC', 'id,title,content,hidden');
            foreach ($chapters as $chapter) {
                if (empty($chapter->hidden)) {
                    $contentparts[] = (string)$chapter->title . "\n" . (string)$chapter->content;
                }
            }
        }

        $html = implode("\n", $contentparts);
        $dates = [];
        $datefields = [
            'allowsubmissionsfromdate', 'duedate', 'cutoffdate', 'timeopen', 'timeclose', 'available', 'deadline',
            'submissionstart', 'submissionend', 'assessmentstart', 'assessmentend', 'timeavailablefrom',
            'timeavailableto', 'timeviewfrom', 'timeviewto',
        ];
        foreach ($datefields as $field) {
            if (isset($record->{$field}) && is_numeric($record->{$field})) {
                $dates[$field] = (int)$record->{$field};
            }
        }

        $grade = null;
        if (isset($record->grade) && is_numeric($record->grade)) {
            $grade = (float)$record->grade;
        }

        $hasgradeitem = $DB->record_exists('grade_items', [
            'courseid' => $course->id,
            'itemtype' => 'mod',
            'itemmodule' => $cm->modname,
            'iteminstance' => $cm->instance,
        ]);

        $externalurl = isset($record->externalurl) ? trim((string)$record->externalurl) : '';
        $urls = $this->extract_urls($html);
        if ($externalurl !== '') {
            $urls[] = $externalurl;
        }

        return [
            'cmid' => (int)$cm->id,
            'instanceid' => (int)$cm->instance,
            'modname' => (string)$cm->modname,
            'name' => text_sanitizer::plain($cm->name, 500),
            'visible' => $cm->visible,
            'visibleoncoursepage' => $cm->visibleoncoursepage,
            'availability' => (string)($cm->availability ?? ''),
            'completion' => (int)($cm->completion ?? 0),
            'introhtml' => $intro,
            'contenthtml' => $html,
            'text' => text_sanitizer::plain($html, 12000),
            'dates' => $dates,
            'grade' => $grade,
            'hasgradeitem' => $hasgradeitem,
            'urls' => array_values(array_unique($urls)),
            'editurl' => (new moodle_url('/course/modedit.php', ['update' => $cm->id, 'return' => 1]))->out(false),
        ];
    }

    /**
     * Fetch only known fields from a module instance table.
     *
     * @param cm_info $cm
     * @return stdClass
     */
    private function get_instance_record(cm_info $cm): stdClass {
        global $DB;

        $table = (string)$cm->modname;
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $table) || !$DB->get_manager()->table_exists(new xmldb_table($table))) {
            return (object)[];
        }

        $columns = $DB->get_columns($table);
        $wanted = [
            'id', 'intro', 'content', 'externalurl', 'grade', 'allowsubmissionsfromdate', 'duedate', 'cutoffdate',
            'timeopen', 'timeclose', 'available', 'deadline', 'submissionstart', 'submissionend', 'assessmentstart',
            'assessmentend', 'timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto',
        ];
        $fields = array_values(array_filter($wanted, static fn(string $field): bool => isset($columns[$field])));
        if (!$fields) {
            return (object)[];
        }

        return $DB->get_record($table, ['id' => $cm->instance], implode(',', $fields), IGNORE_MISSING) ?: (object)[];
    }

    /**
     * Extract href/src URLs without making network requests.
     *
     * @param string $html
     * @return array
     */
    private function extract_urls(string $html): array {
        if ($html === '') {
            return [];
        }
        preg_match_all('/(?:href|src)\s*=\s*(["\'])(.*?)\1/isu', $html, $matches);
        return array_values(array_filter(array_map('trim', $matches[2] ?? [])));
    }
}
