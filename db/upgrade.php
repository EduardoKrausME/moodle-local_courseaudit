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
 * Upgrade steps for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_courseaudit.
 *
 * @param int $oldversion Old version.
 * @return bool
 */
function xmldb_local_courseaudit_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026100600) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_courseaudit_analysis');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('cmid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('analysis_type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, 'full');
            $table->add_field('contenthash', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL);
            $table->add_field('status', XMLDB_TYPE_CHAR, '80');
            $table->add_field('statuskey', XMLDB_TYPE_CHAR, '30');
            $table->add_field('bloomlevel', XMLDB_TYPE_CHAR, '30');
            $table->add_field('model', XMLDB_TYPE_CHAR, '100');
            $table->add_field('prompttokens', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('completiontokens', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('recommendations', XMLDB_TYPE_TEXT);
            $table->add_field('resulttext', XMLDB_TYPE_TEXT);
            $table->add_field('resultjson', XMLDB_TYPE_TEXT);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('courseid', XMLDB_INDEX_NOTUNIQUE, ['courseid']);
            $table->add_index('cmid', XMLDB_INDEX_NOTUNIQUE, ['cmid']);
            $table->add_index('cm_hash', XMLDB_INDEX_NOTUNIQUE, ['cmid', 'contenthash']);
            $table->add_index('course_type_time', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'analysis_type', 'timecreated']);
            $dbman->create_table($table);
        }

        $oldtable = new xmldb_table('local_geniai_analysis');
        if ($dbman->table_exists($oldtable)) {
            $records = $DB->get_records('local_geniai_analysis', [], 'id ASC');
            foreach ($records as $record) {
                $exists = $DB->record_exists('local_courseaudit_analysis', [
                    'courseid' => $record->courseid,
                    'cmid' => $record->cmid,
                    'userid' => $record->userid,
                    'analysis_type' => $record->analysis_type,
                    'contenthash' => $record->contenthash,
                    'timecreated' => $record->timecreated,
                ]);
                if ($exists) {
                    continue;
                }

                unset($record->id);
                $DB->insert_record('local_courseaudit_analysis', $record);
            }

            $dbman->drop_table($oldtable);
        }

        if (get_config('local_courseaudit', 'analysis_excluded_plugins') === false) {
            $oldconfig = get_config('local_geniai', 'analysis_excluded_plugins');
            if ($oldconfig !== false) {
                set_config('analysis_excluded_plugins', $oldconfig, 'local_courseaudit');
            }
        }

        upgrade_plugin_savepoint(true, 2026100600, 'local', 'courseaudit');
    }

    return true;
}
