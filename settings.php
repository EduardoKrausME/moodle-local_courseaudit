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
 * Course Audit settings.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $ADMIN, $CFG, $DB;

    $settings = new admin_settingpage('local_courseaudit', get_string('pluginname', 'local_courseaudit'));

    $modules = [];
    $records = $DB->get_records('modules', ['visible' => 1], 'name', 'name');
    foreach ($records as $record) {
        if (file_exists("{$CFG->dirroot}/mod/{$record->name}/lib.php")
                && plugin_supports('mod', $record->name, FEATURE_MOD_ARCHETYPE) !== MOD_ARCHETYPE_SYSTEM) {
            $modules[$record->name] = get_string('pluginname', $record->name);
        }
    }

    $settings->add(new admin_setting_configmultiselect(
        'local_courseaudit/analysis_excluded_plugins',
        get_string('analysis_excluded_plugins', 'local_courseaudit'),
        get_string('analysis_excluded_plugins_desc', 'local_courseaudit'),
        ['chat'],
        $modules
    ));

    $ADMIN->add('localplugins', $settings);
}
