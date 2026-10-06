<?php
// This file is part of Moodle - http://moodle.org/

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
