<?php
// This file is part of Moodle - http://moodle.org/

namespace local_courseaudit\privacy;

use context;
use context_course;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for Course Audit data.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    core_userlist_provider {

    /**
     * Describe persisted data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_courseaudit_run',
            [
                'courseid' => 'privacy:metadata:runs:courseid',
                'userid' => 'privacy:metadata:runs:userid',
                'mode' => 'privacy:metadata:runs:mode',
                'sectionid' => 'privacy:metadata:runs:sectionid',
                'contenthash' => 'privacy:metadata:runs:contenthash',
                'findingsjson' => 'privacy:metadata:runs:findings',
                'aiused' => 'privacy:metadata:runs:aiused',
                'status' => 'privacy:metadata:runs:status',
                'timecreated' => 'privacy:metadata:runs:timecreated',
            ],
            'privacy:metadata:runs'
        );

        $collection->add_database_table(
            'local_courseaudit_analysis',
            [
                'courseid' => 'privacy:metadata:analysis:courseid',
                'cmid' => 'privacy:metadata:analysis:cmid',
                'userid' => 'privacy:metadata:analysis:userid',
                'contenthash' => 'privacy:metadata:analysis:contenthash',
                'resulttext' => 'privacy:metadata:analysis:result',
                'resultjson' => 'privacy:metadata:analysis:result',
                'timecreated' => 'privacy:metadata:analysis:timecreated',
            ],
            'privacy:metadata:analysis'
        );

        return $collection;
    }

    /**
     * Get contexts containing user data.
     *
     * @param int $userid User id.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_courseaudit_run} r ON r.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND r.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);

        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {local_courseaudit_analysis} a ON a.courseid = ctx.instanceid
                 WHERE ctx.contextlevel = :contextlevel
                   AND a.userid = :userid";
        $contextlist->add_from_sql($sql, ['contextlevel' => CONTEXT_COURSE, 'userid' => $userid]);

        return $contextlist;
    }

    /**
     * Export user data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (!$contextlist->count()) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_course) {
                continue;
            }

            $records = $DB->get_records('local_courseaudit_run', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');

            foreach ($records as $record) {
                writer::with_context($context)->export_data([
                    get_string('privacy:path', 'local_courseaudit'),
                    (string)$record->id,
                ], (object)[
                    'mode' => $record->mode,
                    'sectionid' => $record->sectionid,
                    'findings' => json_decode($record->findingsjson, true) ?: [],
                    'aiused' => transform::yesno($record->aiused),
                    'status' => $record->status,
                    'timecreated' => transform::datetime($record->timecreated),
                ]);
            }

            $analyses = $DB->get_records('local_courseaudit_analysis', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');

            foreach ($analyses as $analysis) {
                writer::with_context($context)->export_data([
                    get_string('privacy:analysispath', 'local_courseaudit'),
                    (string)$analysis->id,
                ], (object)[
                    'cmid' => $analysis->cmid,
                    'analysis_type' => $analysis->analysis_type,
                    'status' => $analysis->status,
                    'bloomlevel' => $analysis->bloomlevel,
                    'recommendations' => json_decode($analysis->recommendations ?? '[]', true) ?: [],
                    'content' => $analysis->resulttext,
                    'timecreated' => transform::datetime($analysis->timecreated),
                ]);
            }
        }
    }

    /**
     * Delete data for all users in a context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $DB->delete_records('local_courseaudit_run', ['courseid' => $context->instanceid]);
        $DB->delete_records('local_courseaudit_analysis', ['courseid' => $context->instanceid]);
    }

    /**
     * Delete data for one user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_COURSE) {
                continue;
            }

            $DB->delete_records('local_courseaudit_run', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ]);
            $DB->delete_records('local_courseaudit_analysis', [
                'courseid' => $context->instanceid,
                'userid' => $userid,
            ]);
        }
    }

    /**
     * Add users represented in a context.
     *
     * @param userlist $userlist User list.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $sql = "SELECT r.userid
                  FROM {local_courseaudit_run} r
                 WHERE r.courseid = :courseid";
        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);

        $sql = "SELECT a.userid
                  FROM {local_courseaudit_analysis} a
                 WHERE a.courseid = :courseid";
        $userlist->add_from_sql('userid', $sql, ['courseid' => $context->instanceid]);
    }

    /**
     * Delete data for an approved user list.
     *
     * @param approved_userlist $userlist Approved users.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_COURSE) {
            return;
        }

        $userids = $userlist->get_userids();
        if (!$userids) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['courseid'] = $context->instanceid;
        $DB->delete_records_select('local_courseaudit_run', "courseid = :courseid AND userid {$insql}", $params);
        $DB->delete_records_select('local_courseaudit_analysis', "courseid = :courseid AND userid {$insql}", $params);
    }
}
