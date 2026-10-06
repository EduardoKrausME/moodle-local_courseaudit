<?php
// This file is part of Moodle - http://moodle.org/

use core\hook\output\before_footer_html_generation;

defined('MOODLE_INTERNAL') || die;

$callbacks = [
    [
        'hook' => before_footer_html_generation::class,
        'callback' => '\\local_courseaudit\\core_hook_output::before_footer_html_generation',
    ],
];
