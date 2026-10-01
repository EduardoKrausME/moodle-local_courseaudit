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
use local_courseaudit\text_sanitizer;

/**
 * Tests for text sanitization.
 *
 * @covers \\local_courseaudit\\text_sanitizer
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class text_sanitizer_test extends advanced_testcase {
    /**
     * Normalize HTML and whitespace predictably.
     */
    public function test_plain_removes_markup_and_script_content(): void {
        $plain = text_sanitizer::plain('<p>Hello <strong>world</strong></p><script>bad()</script>');
        $this->assertSame('Hello world', $plain);
    }

    /**
     * Normalize equivalent content for deterministic hashes.
     */
    public function test_normalize_is_case_and_punctuation_insensitive(): void {
        $this->assertSame(
            text_sanitizer::normalize('Hello, WORLD!'),
            text_sanitizer::normalize('hello world')
        );
    }
}
