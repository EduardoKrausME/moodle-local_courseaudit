<?php
// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die;

$functions = [
    'local_courseaudit_analyze_activity' => [
        'classpath' => 'local/courseaudit/classes/external/analyze_activity.php',
        'classname' => '\\local_courseaudit\\external\\analyze_activity',
        'methodname' => 'api',
        'description' => 'Analyze one Moodle activity with Course Audit AI',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_courseaudit_analyze_course' => [
        'classpath' => 'local/courseaudit/classes/external/analyze_course.php',
        'classname' => '\\local_courseaudit\\external\\analyze_course',
        'methodname' => 'api',
        'description' => 'Analyze visible Moodle course activities with Course Audit AI',
        'type' => 'read',
        'ajax' => true,
    ],
    'local_courseaudit_analysis_history' => [
        'classpath' => 'local/courseaudit/classes/external/analysis_history.php',
        'classname' => '\\local_courseaudit\\external\\analysis_history',
        'methodname' => 'api',
        'description' => 'Read Course Audit activity analysis history',
        'type' => 'read',
        'ajax' => true,
    ],
];
