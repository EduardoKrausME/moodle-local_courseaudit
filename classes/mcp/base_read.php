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
 * Shared MCP access checks for course audit reports.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\mcp;

use context;
use context_course;
use local_mcp\read\tool\base_tool;
use local_mcp\security\authenticated_identity;

/**
 * Require the existing course-level audit capability for every READ call.
 */
abstract class base_read extends base_tool {
    /**
     * @return string
     */
    public function get_required_capability(): string {
        return "local/courseaudit:audit";
    }

    /**
     * @param array $arguments Tool parameters.
     * @return context
     */
    public function resolve_context(array $arguments): context {
        $courseid = (int)$arguments["courseid"];
        get_course($courseid);
        return context_course::instance($courseid, MUST_EXIST);
    }

    /**
     * Check access again inside the tool, including direct invocation paths.
     *
     * @param array $arguments Tool parameters.
     * @param authenticated_identity $identity Connected Moodle user.
     * @return void
     */
    protected function assert_access(array $arguments, authenticated_identity $identity): void {
        require_capability("local/courseaudit:audit", $this->resolve_context($arguments), $identity->userid);
    }
}
