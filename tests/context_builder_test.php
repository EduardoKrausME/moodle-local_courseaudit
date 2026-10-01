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
use local_courseaudit\local\context_builder;

/**
 * Tests for AI context normalization.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class context_builder_test extends advanced_testcase {
    /**
    * Remove common identifiers and secrets before AI use.
    */
    public function test_context_redacts_email_and_tokens(): void {
        $snapshot = [
            'course' => [
                'fullname' => 'Course',
                'summary' => 'Contact teacher@example.com password=abc123',
            ],
            'sections' => [[
                'name' => 'Section',
                'summary' => 'Bearer abc.def.ghi',
                'modules' => [[
                    'modname' => 'page',
                    'name' => 'Page',
                    'text' => 'API_KEY=secret-value and learner@example.com',
                ]],
            ]],
        ];

        $context = (new context_builder())->build($snapshot);
        $encoded = json_encode($context);
        $this->assertStringNotContainsString('teacher@example.com', $encoded);
        $this->assertStringNotContainsString('learner@example.com', $encoded);
        $this->assertStringNotContainsString('secret-value', $encoded);
        $this->assertStringNotContainsString('abc.def.ghi', $encoded);
    }
}
