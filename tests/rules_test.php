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

namespace local_courseaudit;

use advanced_testcase;
use local_courseaudit\local\finding;
use local_courseaudit\rules\content_rule;
use local_courseaudit\rules\dates_rule;

/**
 * Tests for deterministic rules.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class rules_test extends advanced_testcase {
    /**
    * Test that due/close dates before opening dates are objective errors.
    */
    public function test_dates_rule_detects_invalid_order(): void {
        $snapshot = [
            'course' => ['startdate' => 100, 'enddate' => 200],
            'sections' => [[
                'modules' => [[
                    'modname' => 'assign',
                    'name' => 'Assignment A',
                    'dates' => ['allowsubmissionsfromdate' => 200, 'duedate' => 100],
                    'editurl' => 'https://example.invalid/edit',
                ]],
            ]],
        ];

        $findings = (new dates_rule())->run($snapshot);
        $this->assertNotEmpty($findings);
        $this->assertSame(finding::ERROR, $findings[0]->type);
        $this->assertSame('dates', $findings[0]->category);
    }

    /**
    * Test literal duplicate detection before any AI analysis.
    */
    public function test_content_rule_detects_identical_normalized_content(): void {
        $longtext = str_repeat('This deterministic paragraph has meaningful duplicated course content. ', 3);
        $snapshot = [
            'sections' => [[
                'modules' => [
                    [
                        'modname' => 'page', 'name' => 'Page A', 'text' => $longtext,
                        'introhtml' => '<p>Intro</p>', 'editurl' => 'https://example.invalid/a',
                    ],
                    [
                        'modname' => 'page', 'name' => 'Page B', 'text' => strtoupper($longtext),
                        'introhtml' => '<p>Intro</p>', 'editurl' => 'https://example.invalid/b',
                    ],
                ],
            ]],
        ];

        $findings = (new content_rule())->run($snapshot);
        $duplicates = array_filter($findings, static fn(finding $finding): bool => $finding->category === 'content' && $finding->type === finding::WARNING);
        $this->assertCount(1, $duplicates);
    }
}
