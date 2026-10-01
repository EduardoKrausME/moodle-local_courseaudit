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
use JsonException;
use local_courseaudit\ai\response_parser;
use local_courseaudit\finding;

/**
 * Tests for strict AI JSON parsing.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class response_parser_test extends advanced_testcase {
    /**
     * Parse a valid structured response and strip returned HTML.
     */
    public function test_parse_valid_json_and_sanitise_html(): void {
        $json = json_encode([
            'findings' => [[
                'type' => 'ai_insight',
                'category' => 'pedagogy',
                'title' => '<b>Sequence</b>',
                'description' => '<script>alert(1)</script>The sequence may need review.',
                'evidence' => ['<strong>Section 2</strong> precedes Section 1 conceptually.'],
                'suggestion' => '<em>Review order</em>',
            ]],
        ]);

        $findings = (new response_parser())->parse($json);
        $this->assertCount(1, $findings);
        $this->assertSame('Sequence', $findings[0]->title);
        $this->assertStringNotContainsString('<', $findings[0]->description);
        $this->assertSame(finding::SOURCE_AI, $findings[0]->source);
    }

    /**
     * AI is not allowed to create objective ERROR findings.
     */
    public function test_ai_error_is_downgraded_to_warning(): void {
        $json = '{"findings":[{"type":"error","category":"pedagogy","title":"Claim","description":"Interpretive claim"}]}';
        $findings = (new response_parser())->parse($json);
        $this->assertSame(finding::WARNING, $findings[0]->type);
    }

    /**
     * Invalid JSON must never be silently rendered or accepted.
     */
    public function test_invalid_json_throws(): void {
        $this->expectException(JsonException::class);
        (new response_parser())->parse('not-json');
    }
}
