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
 * List the connected user's saved course-audit history.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\mcp;

use local_mcp\security\authenticated_identity;

/**
 * List report metadata without running audits or consuming AI credits.
 */
final class list_runs extends base_read {
    /**
     * Return the MCP tool identifier.
     *
     * @return string
     */
    public function get_name(): string {
        return "courseaudit_list_runs";
    }

    /**
     * Return the translated MCP tool title.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string("mcplistruns", "local_courseaudit");
    }

    /**
     * Return the translated MCP tool description.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string("mcplistrunsdesc", "local_courseaudit");
    }

    /**
     * Declare the validated MCP argument schema.
     *
     * @return array
     */
    public function get_input_schema(): array {
        return $this->object_schema([
            "courseid" => ["type" => "integer", "minimum" => 1],
            "limit" => ["type" => "integer", "minimum" => 1, "maximum" => 50],
            "offset" => ["type" => "integer", "minimum" => 0],
        ], ["courseid"]);
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
        $limit = max(1, min(50, (int)($arguments["limit"] ?? 20)));
        $offset = max(0, (int)($arguments["offset"] ?? 0));
        return (new run_history())->list_runs(
            (int)$arguments["courseid"], $identity->userid, $limit, $offset
        );
    }
}
