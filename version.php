<?php
// This file is part of Moodle - http://moodle.org/

/**
 * Version metadata for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version = 2026100600;
$plugin->release = '1.1.0';
$plugin->component = 'local_courseaudit';
$plugin->requires = 2024100700;
$plugin->maturity = MATURITY_STABLE;
$plugin->dependencies = [
    'local_ai_bridge' => 2026093001,
];
