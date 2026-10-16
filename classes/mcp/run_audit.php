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
 * Run a confirmed Moodle course audit from MCP.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\mcp;

use context;
use context_course;
use local_courseaudit\audit_manager;
use local_courseaudit\finding;
use local_mcp\security\authenticated_identity;
use local_mcp\write\tool\base_tool;
use moodle_exception;

/**
 * Audits may persist history and consume AI credits, so they must remain WRITE tools.
 */
final class run_audit extends base_tool {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "courseaudit_run_audit";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcprunaudit", "local_courseaudit");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcprunauditdesc", "local_courseaudit");
    }

    /**
     * Return the required course-level audit capability.
     *
     * @return string
     */
    public function get_required_capability(): string {
        return "local/courseaudit:audit";
    }

    /**
     * Declare the validated MCP argument schema.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "courseid" => ["type" => "integer", "minimum" => 1],
            "mode" => ["type" => "string", "enum" => audit_manager::MODES,
                "description" => "Defaults to structure (deterministic checks, no AI credits)."],
            "sectionid" => ["type" => "integer", "minimum" => 0,
                "description" => "A course_sections.id, or zero to audit the entire course."],
            "force" => ["type" => "boolean",
                "description" => "Bypass cached report reuse; may consume additional AI credits."],
        ], ["courseid"]);
    }

    /**
     * Resolve the course context for capability enforcement.
     *
     * @param array $arguments Tool parameters.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        $courseid = (int)$arguments["courseid"];
        get_course($courseid);
        return context_course::instance($courseid, MUST_EXIST);
    }

    /**
     * Validate that the selected section belongs to this course.
     *
     * @param array $arguments Tool parameters.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return array
     */
    private function validated_arguments(array $arguments, authenticated_identity $identity): array {
        global $DB;
        $courseid = (int)$arguments["courseid"];
        require_capability("local/courseaudit:audit", $this->resolve_context($arguments), $identity->userid);
        $mode = (string)($arguments["mode"] ?? "structure");
        if (!in_array($mode, audit_manager::MODES, true)) {
            throw new moodle_exception("mcpinvalidmode", "local_courseaudit");
        }
        $sectionid = (int)($arguments["sectionid"] ?? 0);
        if ($sectionid < 0 || ($sectionid > 0 && !$DB->record_exists("course_sections", [
                "id" => $sectionid, "course" => $courseid]))) {
            throw new moodle_exception("mcpinvalidsection", "local_courseaudit");
        }
        return [
            "courseid" => $courseid,
            "mode" => $mode,
            "sectionid" => $sectionid,
            "force" => (bool)($arguments["force"] ?? false),
        ];
    }

    /**
     * No persistence or AI call before confirmation.
     *
     * @param array $arguments Tool parameters.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return array
     */
    public function preview(array $arguments, authenticated_identity $identity): array {
        $values = $this->validated_arguments($arguments, $identity);
        return [
            "operation" => $this->get_name(),
            "courseid" => $values["courseid"],
            "mode" => $values["mode"],
            "sectionid" => $values["sectionid"],
            "force" => $values["force"],
            "may_call_ai_and_consume_credits" => $values["mode"] !== "structure",
            "may_persist_audit_history" => true,
            "may_reuse_cached_report" => !$values["force"],
            "course_content_will_not_be_modified" => true,
        ];
    }

    /**
     * Execute the existing audit manager, never custom ad hoc rules.
     *
     * @param array $arguments Tool parameters.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $values = $this->validated_arguments($arguments, $identity);
        $course = get_course($values["courseid"]);
        $result = (new audit_manager())->run(
            $course, $identity->userid, $values["mode"], $values["sectionid"], $values["force"]
        );
        $status = $result["aierror"] === null ? "complete" : "partial";
        $runid = (new run_history())->latest_matching(
            $values["courseid"], $identity->userid, $values["mode"],
            $values["sectionid"], $result["contenthash"], $status
        );
        return [
            "courseid" => $values["courseid"],
            "runid" => $runid,
            "mode" => $values["mode"],
            "sectionid" => $values["sectionid"],
            "status" => $status,
            "cached" => (bool)$result["cached"],
            "aiused" => (bool)$result["aiused"],
            "aierror" => $result["aierror"],
            "contenthash" => (string)$result["contenthash"],
            "counts" => $result["stats"],
            "total_findings" => count($result["findings"]),
            "findings_preview" => array_map(
                static fn(finding $item): array => $item->to_array(),
                array_slice($result["findings"], 0, 10)
            ),
            "remaining_findings" => max(0, count($result["findings"]) - 10),
            "read_tool" => "courseaudit_get_report",
        ];
    }
}
