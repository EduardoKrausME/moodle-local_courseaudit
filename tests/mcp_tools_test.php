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
 * Regression tests for optional course-audit MCP integration.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit;

use advanced_testcase;
use context_course;
use local_courseaudit\mcp\run_history;
use moodle_exception;

/**
 * MCP history remains private to the initiating user and course.
 *
 * @covers \local_courseaudit\mcp\run_history
 * @covers \local_courseaudit\mcp\run_audit
 */
final class mcp_tools_test extends advanced_testcase {
    /**
     * Restrict list/read requests to the connected user's records.
     *
     * @return void
     */
    public function test_history_is_scoped_to_course_and_user(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $othercourse = $this->getDataGenerator()->create_course();
        $firstuser = $this->getDataGenerator()->create_user();
        $otheruser = $this->getDataGenerator()->create_user();

        $repository = new audit_repository();
        $runid = $repository->save(
            (int)$course->id, (int)$firstuser->id, "structure", 0,
            str_repeat("a", 64),
            [new finding(finding::WARNING, "structure", "Empty section", "No activities")],
            false, "complete"
        );
        $history = new run_history();
        $first = $history->list_runs((int)$course->id, (int)$firstuser->id, 10, 0);
        $this->assertSame(1, $first["total"]);
        $this->assertSame($runid, $first["runs"][0]["runid"]);
        $this->assertSame(0, $history->list_runs(
            (int)$course->id, (int)$otheruser->id, 10, 0
        )["total"]);

        $report = $history->get_report((int)$course->id, (int)$firstuser->id, $runid, "", 0, 10);
        $this->assertSame(1, $report["total_filtered"]);
        $this->assertSame("Empty section", $report["findings"][0]["title"]);
        $this->assertSame(1, $report["counts"][finding::WARNING]);
        $this->assertSame(0, $history->get_report(
            (int)$course->id, (int)$firstuser->id, $runid, finding::ERROR, 0, 10
        )["total_filtered"]);

        try {
            $history->get_report((int)$course->id, (int)$otheruser->id, $runid, "", 0, 10);
            $this->fail("Another user could read a private run");
        } catch (moodle_exception $exception) {
            $this->assertSame("mcprunmissing", $exception->errorcode);
        }

        $this->expectException(moodle_exception::class);
        $history->get_report((int)$othercourse->id, (int)$firstuser->id, $runid, "", 0, 10);
    }

    /**
     * WRITE preview must not insert an audit run or invoke AI.
     *
     * @return void
     */
    public function test_mcp_preview_does_not_persist(): void {
        global $DB;
        $this->resetAfterTest();
        if (!class_exists(\local_mcp\security\authenticated_identity::class)) {
            $this->markTestSkipped("Optional local_mcp is not installed");
        }
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $identity = new \local_mcp\security\authenticated_identity(
            (int)$this->getUser()->id, "oauth", ["read", "write"]
        );
        $tool = new \local_courseaudit\mcp\run_audit();
        $before = $DB->count_records("local_courseaudit_run");
        $preview = $tool->preview(["courseid" => $course->id, "mode" => "structure"], $identity);
        $this->assertFalse($preview["may_call_ai_and_consume_credits"]);
        $this->assertSame($before, $DB->count_records("local_courseaudit_run"));

        $result = $tool->execute(["courseid" => $course->id, "mode" => "structure"], $identity);
        $this->assertSame("complete", $result["status"]);
        $this->assertFalse($result["aiused"]);
        $this->assertGreaterThan($before, $DB->count_records("local_courseaudit_run"));
        $this->assertNotNull($result["runid"]);
    }
}
