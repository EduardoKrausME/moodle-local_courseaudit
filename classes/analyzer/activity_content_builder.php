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
 * Authored activity content extraction for pedagogical analysis.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\analyzer;

use cm_info;
use context_module;
use dml_exception;
use stdClass;
use Throwable;
use xmldb_table;

/**
 * Extract authored activity content for pedagogical analysis without learner data.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_content_builder {
    /** Maximum authored child items included in one activity snapshot. */
    private const MAX_ITEMS = 20;

    /** Maximum readable files included in one activity snapshot. */
    private const MAX_FILES = 10;

    /** Maximum approved glossary entries included in one activity snapshot. */
    private const MAX_GLOSSARY_ENTRIES = 25;

    /** Maximum current wiki pages included in one activity snapshot. */
    private const MAX_WIKI_PAGES = 10;

    /**
     * Build activity content.
     *
     * @param cm_info $cm Course module.
     * @param stdClass $course Course.
     * @param int $userid User id used by modinfo.
     * @return activity_content
     */
    public static function build(cm_info $cm, stdClass $course, int $userid): activity_content {
        global $DB;

        $content = self::base_content($cm, $course, $userid);
        $record = self::get_activity_record($cm);

        if ($record) {
            if (isset($record->intro)) {
                $content->intro = content_cleaner::clean_html($record->intro);
            }
            if (isset($record->content)) {
                $content->maincontent = content_cleaner::clean_html($record->content);
            }
        } else if (isset($cm->summary)) {
            $content->intro = content_cleaner::clean_html($cm->summary);
        }

        switch ($cm->modname) {
            case 'page':
                if ($record) {
                    $content->maincontent = content_cleaner::clean_html($record->content ?? '');
                }
                break;

            case 'label':
                if ($record) {
                    $content->maincontent = $content->intro;
                }
                break;

            case 'assign':
                if ($record) {
                    $content->metadata['duedate'] = !empty($record->duedate) ? userdate($record->duedate) : '';
                    $content->metadata['allowsubmissionsfromdate'] = !empty($record->allowsubmissionsfromdate)
                        ? userdate($record->allowsubmissionsfromdate) : '';
                    $content->metadata['grade'] = $record->grade ?? '';
                }
                break;

            case 'forum':
                if ($record) {
                    $content->metadata['forumtype'] = $record->type ?? '';
                    $content->metadata['scale'] = $record->scale ?? '';
                }
                break;

            case 'quiz':
                if ($record) {
                    $content->metadata['grade'] = $record->grade ?? '';
                    $content->metadata['sumgrades'] = $record->sumgrades ?? '';
                    $content->questions = self::quiz_questions((int)$record->id);
                }
                break;

            case 'book':
                if ($record && self::table_exists('book_chapters')) {
                    $chapters = $DB->get_records(
                        'book_chapters',
                        ['bookid' => $record->id],
                        'pagenum ASC',
                        '*',
                        0,
                        self::MAX_ITEMS
                    );
                    $parts = [];
                    foreach ($chapters as $chapter) {
                        if (!empty($chapter->hidden)) {
                            continue;
                        }
                        $title = content_cleaner::normalize_text(format_string($chapter->title));
                        $parts[] = "## {$title}\n" . content_cleaner::clean_html($chapter->content);
                    }
                    $content->maincontent = content_cleaner::limit(implode("\n\n", $parts), 18000);
                    $content->metadata['chapters_extracted'] = count($parts);
                }
                break;

            case 'lesson':
                if ($record && self::table_exists('lesson_pages')) {
                    $pages = $DB->get_records(
                        'lesson_pages',
                        ['lessonid' => $record->id],
                        'prevpageid ASC, id ASC',
                        '*',
                        0,
                        self::MAX_ITEMS
                    );
                    $parts = [];
                    foreach ($pages as $page) {
                        $title = content_cleaner::normalize_text(format_string($page->title ?? ''));
                        $parts[] = "## {$title}\n" . content_cleaner::clean_html($page->contents ?? '');
                    }
                    $content->maincontent = content_cleaner::limit(implode("\n\n", $parts), 18000);
                    $content->metadata['pages_extracted'] = count($parts);
                }
                break;

            case 'url':
                if ($record) {
                    $content->metadata['externalurl'] = (string)($record->externalurl ?? '');
                }
                break;

            case 'resource':
                $content->maincontent = self::files_text($cm, 'mod_resource', 'content');
                break;

            case 'folder':
                $content->maincontent = self::files_text($cm, 'mod_folder', 'content');
                break;

            case 'h5pactivity':
                if ($record) {
                    $content->metadata['packagefileid'] = $record->packagefile ?? '';
                    $content->maincontent =
                        'H5P package content is not deeply extracted in this version. Analyze title, intro and section alignment.';
                }
                break;

            case 'wiki':
                if ($record) {
                    $content->metadata['wikimode'] = $record->wikimode ?? '';
                    $content->metadata['firstpagetitle'] = $record->firstpagetitle ?? '';
                    $pages = self::wiki_pages((int)$record->id);
                    $content->maincontent = $pages['content'];
                    $content->metadata['pages_extracted'] = $pages['count'];
                }
                break;

            case 'glossary':
                if ($record && self::table_exists('glossary_entries')) {
                    $entries = $DB->get_records(
                        'glossary_entries',
                        ['glossaryid' => $record->id, 'approved' => 1],
                        'concept ASC',
                        'id,concept,definition',
                        0,
                        self::MAX_GLOSSARY_ENTRIES
                    );
                    $parts = [];
                    foreach ($entries as $entry) {
                        $parts[] = '## ' . content_cleaner::normalize_text($entry->concept)
                            . "\n" . content_cleaner::clean_html($entry->definition);
                    }
                    $content->maincontent = content_cleaner::limit(implode("\n\n", $parts), 18000);
                    $content->metadata['entries_extracted'] = count($parts);
                }
                break;

            case 'feedback':
                if ($record && self::table_exists('feedback_item')) {
                    $items = $DB->get_records(
                        'feedback_item',
                        ['feedback' => $record->id],
                        'position ASC',
                        'id,name,label,typ',
                        0,
                        self::MAX_ITEMS
                    );
                    $parts = [];
                    foreach ($items as $item) {
                        $label = content_cleaner::normalize_text($item->label ?? '');
                        $name = content_cleaner::normalize_text($item->name ?? '');
                        $parts[] = trim(($label !== '' ? $label . ': ' : '') . $name);
                    }
                    $content->maincontent = content_cleaner::limit(implode("\n", array_filter($parts)), 18000);
                    $content->metadata['items_extracted'] = count($parts);
                }
                break;
        }

        return $content;
    }

    /**
     * Build common metadata.
     *
     * @param cm_info $cm Course module.
     * @param stdClass $course Course.
     * @param int $userid User id.
     * @return activity_content
     */
    private static function base_content(cm_info $cm, stdClass $course, int $userid): activity_content {
        $content = new activity_content();
        $content->courseid = (int)$course->id;
        $content->cmid = (int)$cm->id;
        $content->instanceid = (int)$cm->instance;
        $content->modname = (string)$cm->modname;
        $content->activityname = content_cleaner::normalize_text(format_string($cm->name));
        $content->coursefullname = content_cleaner::normalize_text(format_string($course->fullname));
        $content->courseshortname = content_cleaner::normalize_text(format_string($course->shortname));
        $content->url = $cm->url ? $cm->url->out(false) : '';

        $modinfo = get_fast_modinfo($course, $userid);
        if (isset($cm->sectionnum)) {
            $section = $modinfo->get_section_info($cm->sectionnum);
            if ($section) {
                $content->sectionname = content_cleaner::normalize_text(get_section_name($course, $section));
                $content->sectionsummary = content_cleaner::clean_html($section->summary ?? '');
            }
        }

        $content->metadata = [
            'visible' => $cm->visible ? 1 : 0,
            'uservisible' => $cm->uservisible ? 1 : 0,
            'moduleidnumber' => $cm->idnumber ?? '',
        ];
        return $content;
    }

    /**
     * Get module instance record.
     *
     * @param cm_info $cm Course module.
     * @return stdClass|false
     */
    private static function get_activity_record(cm_info $cm) {
        global $DB;

        if (!preg_match('/^[a-z][a-z0-9_]*$/', $cm->modname) || !self::table_exists($cm->modname)) {
            return false;
        }

        try {
            return $DB->get_record($cm->modname, ['id' => $cm->instance]);
        } catch (dml_exception) {
            return false;
        }
    }

    /**
     * Extract current Wiki pages without reading version history.
     *
     * @param int $wikiid Wiki id.
     * @return array{content: string, count: int}
     */
    private static function wiki_pages(int $wikiid): array {
        global $DB;

        if (!self::table_exists('wiki_subwikis')
                || !self::table_exists('wiki_pages')
                || !self::table_exists('wiki_versions')) {
            return ['content' => '', 'count' => 0];
        }

        $sql = "SELECT p.id, p.title, v.content
                  FROM {wiki_subwikis} sw
                  JOIN {wiki_pages} p ON p.subwikiid = sw.id
                  JOIN {wiki_versions} v ON v.pageid = p.id AND v.version = p.cachedcontentversion
                 WHERE sw.wikiid = :wikiid
              ORDER BY p.title ASC";

        try {
            $records = $DB->get_records_sql($sql, ['wikiid' => $wikiid], 0, self::MAX_WIKI_PAGES);
        } catch (dml_exception) {
            return ['content' => '', 'count' => 0];
        }

        $parts = [];
        foreach ($records as $record) {
            $title = content_cleaner::normalize_text($record->title);
            $parts[] = "## {$title}\n" . content_cleaner::clean_html($record->content);
        }

        return [
            'content' => content_cleaner::limit(implode("\n\n", $parts), 18000),
            'count' => count($parts),
        ];
    }

    /**
     * Extract quiz questions across supported Moodle question schemas.
     *
     * @param int $quizid Quiz id.
     * @return array
     */
    private static function quiz_questions(int $quizid): array {
        global $DB;

        if (!self::table_exists('quiz_slots')) {
            return [];
        }

        try {
            $columns = $DB->get_columns('quiz_slots');
            if (isset($columns['questionid'])) {
                $sql = "SELECT q.id, q.name, q.qtype, q.questiontext
                          FROM {quiz_slots} qs
                          JOIN {question} q ON q.id = qs.questionid
                         WHERE qs.quizid = :quizid
                      ORDER BY qs.slot";
                $records = $DB->get_records_sql($sql, ['quizid' => $quizid], 0, self::MAX_ITEMS);
            } else {
                $sql = "SELECT q.id, q.name, q.qtype, q.questiontext, qs.slot
                          FROM {quiz_slots} qs
                          JOIN {question_references} qr
                            ON qr.itemid = qs.id
                           AND qr.component = :component
                           AND qr.questionarea = :questionarea
                          JOIN {question_bank_entries} qbe ON qbe.id = qr.questionbankentryid
                          JOIN {question_versions} qv ON qv.questionbankentryid = qbe.id
                          JOIN {question} q ON q.id = qv.questionid
                         WHERE qs.quizid = :quizid
                           AND (qr.version IS NULL OR qr.version = qv.version)
                           AND (qv.status IS NULL OR qv.status <> :draftstatus)
                      ORDER BY qs.slot ASC, qv.version DESC";
                $records = $DB->get_records_sql($sql, [
                    'quizid' => $quizid,
                    'component' => 'mod_quiz',
                    'questionarea' => 'slot',
                    'draftstatus' => 'draft',
                ], 0, self::MAX_ITEMS);
            }
        } catch (dml_exception) {
            return [];
        }

        $questions = [];
        foreach ($records as $record) {
            $questions[] = [
                'name' => content_cleaner::normalize_text(format_string($record->name)),
                'qtype' => (string)($record->qtype ?? ''),
                'questiontext' => content_cleaner::clean_html($record->questiontext),
            ];
        }
        return $questions;
    }

    /**
     * Extract safe text files from one module file area.
     *
     * @param cm_info $cm Course module.
     * @param string $component File component.
     * @param string $filearea File area.
     * @return string
     */
    private static function files_text(cm_info $cm, string $component, string $filearea): string {
        try {
            $context = context_module::instance($cm->id);
            $files = get_file_storage()->get_area_files($context->id, $component, $filearea, 0, 'filename', false);
        } catch (Throwable) {
            return '';
        }

        $parts = [];
        $count = 0;
        foreach ($files as $file) {
            if ($file->is_directory()) {
                continue;
            }

            $parts[] = 'File: ' . $file->get_filename() . ' (' . $file->get_mimetype() . ')';
            $extension = strtolower(pathinfo($file->get_filename(), PATHINFO_EXTENSION));
            $mimetype = (string)$file->get_mimetype();
            $extensions = ['txt', 'html', 'htm', 'md', 'csv', 'json', 'xml'];

            if (str_starts_with($mimetype, 'text/') || in_array($extension, $extensions, true)) {
                try {
                    $parts[] = content_cleaner::limit(content_cleaner::clean_html($file->get_content()), 12000);
                } catch (Throwable) {
                    $parts[] = '';
                }
            }

            if (++$count >= self::MAX_FILES) {
                break;
            }
        }

        return content_cleaner::limit(implode("\n\n", $parts), 18000);
    }

    /**
     * Check table existence defensively.
     *
     * @param string $name Table name.
     * @return bool
     */
    private static function table_exists(string $name): bool {
        global $DB;

        try {
            return $DB->get_manager()->table_exists(new xmldb_table($name));
        } catch (Throwable) {
            return false;
        }
    }
}
