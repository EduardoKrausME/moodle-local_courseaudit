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
 * Install steps for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Migrate activity-analysis history when Course Audit is installed after GeniAI.
 *
 * @return void
 */
function xmldb_local_courseaudit_install(): void {
    global $DB;

    $dbman = $DB->get_manager();
    $oldtable = new xmldb_table('local_geniai_analysis');
    $newtable = new xmldb_table('local_courseaudit_analysis');

    if ($dbman->table_exists($oldtable) && $dbman->table_exists($newtable)) {
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

    $oldconfig = get_config('local_geniai', 'analysis_excluded_plugins');
    if ($oldconfig !== false && get_config('local_courseaudit', 'analysis_excluded_plugins') === false) {
        set_config('analysis_excluded_plugins', $oldconfig, 'local_courseaudit');
    }
    unset_config('analysis_excluded_plugins', 'local_geniai');
}
