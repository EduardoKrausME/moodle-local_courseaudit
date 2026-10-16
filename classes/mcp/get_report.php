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
 * Read a paginated saved course-audit report through MCP.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\mcp;

use local_courseaudit\finding;
use local_mcp\security\authenticated_identity;
use moodle_exception;

/**
 * Expose structured saved findings only for the user who ran the audit.
 */
final class get_report extends base_read {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "courseaudit_get_report";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcpgetreport", "local_courseaudit");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcpgetreportdesc", "local_courseaudit");
    }

    /**
     * Declare the validated MCP argument schema.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "courseid" => ["type" => "integer", "minimum" => 1],
            "runid" => ["type" => "integer", "minimum" => 1],
            "severity" => ["type" => "string",
                "enum" => ["", finding::ERROR, finding::WARNING, finding::SUGGESTION, finding::AI_INSIGHT]],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => 50],
            "offset" => ["type" => "integer", "minimum" => 0],
        ], ["courseid", "runid"]);
    }

    /**
     * Execute the authorized course-audit operation.
     *
     * @param array $arguments Tool parameters.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return array
     */
    public function execute(array $arguments, authenticated_identity $identity): array {
        $this->assert_access($arguments, $identity);
        $severity = (string)($arguments["severity"] ?? "");
        if (!in_array($severity, ["", finding::ERROR, finding::WARNING,
                finding::SUGGESTION, finding::AI_INSIGHT], true)) {
            throw new moodle_exception("mcpinvalidseverity", "local_courseaudit");
        }
        return (new run_history())->get_report(
            (int)$arguments["courseid"],
            $identity->userid,
            (int)$arguments["runid"],
            $severity,
            max(0, (int)($arguments["offset"] ?? 0)),
            max(1, min(50, (int)($arguments["limit"] ?? 20)))
        );
    }
}
