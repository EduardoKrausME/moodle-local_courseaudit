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
 * Scope and paginate persisted course-audit results for MCP.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\mcp;

use local_courseaudit\finding;
use moodle_exception;

/**
 * Read only the initiating user's saved audit runs, never other users' history.
 */
final class run_history {
    /**
     * List the caller's runs, without loading stored finding bodies.
     *
     * @param int $courseid Course identifier.
     * @param int $userid Authenticated Moodle user identifier.
     * @param int $limit Page length.
     * @param int $offset Page offset.
     * @return array
     */
    public function list_runs(int $courseid, int $userid, int $limit, int $offset): array {
        global $DB;

        $where = ["courseid" => $courseid, "userid" => $userid];
        $total = $DB->count_records("local_courseaudit_run", $where);
        $records = $DB->get_records(
            "local_courseaudit_run", $where, "timecreated DESC, id DESC",
            "id,mode,sectionid,contenthash,aiused,status,timecreated", $offset, $limit
        );
        $runs = [];
        foreach ($records as $record) {
            $runs[] = [
                "runid" => (int)$record->id,
                "mode" => (string)$record->mode,
                "sectionid" => (int)$record->sectionid,
                "contenthash" => (string)$record->contenthash,
                "aiused" => (bool)$record->aiused,
                "status" => (string)$record->status,
                "timecreated" => (int)$record->timecreated,
            ];
        }

        return [
            "courseid" => $courseid,
            "total" => $total,
            "limit" => $limit,
            "offset" => $offset,
            "has_more" => $offset + count($runs) < $total,
            "runs" => $runs,
        ];
    }

    /**
     * Retrieve one run belonging to the connected user in the given course.
     *
     * @param int $courseid Course identifier.
     * @param int $userid Authenticated Moodle user identifier.
     * @param int $runid Saved run identifier.
     * @param string $severity Optional severity filter.
     * @param int $offset Page offset.
     * @param int $limit Page size.
     * @return array
     */
    public function get_report(
        int $courseid, int $userid, int $runid, string $severity, int $offset, int $limit
    ): array {
        global $DB;

        $record = $DB->get_record("local_courseaudit_run", [
            "id" => $runid,
            "courseid" => $courseid,
            "userid" => $userid,
        ]);
        if (!$record) {
            throw new moodle_exception("mcprunmissing", "local_courseaudit");
        }

        $decoded = json_decode((string)$record->findingsjson, true);
        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw new moodle_exception("mcpruncorrupt", "local_courseaudit");
        }

        $selected = [];
        $counts = [
            finding::ERROR => 0,
            finding::WARNING => 0,
            finding::SUGGESTION => 0,
            finding::AI_INSIGHT => 0,
        ];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $item = finding::from_array($row);
            if (array_key_exists($item->type, $counts)) {
                $counts[$item->type]++;
            }
            if ($severity === "" || $item->type === $severity) {
                $selected[] = $item->to_array();
            }
        }

        $total = count($selected);
        return [
            "runid" => (int)$record->id,
            "courseid" => (int)$record->courseid,
            "mode" => (string)$record->mode,
            "sectionid" => (int)$record->sectionid,
            "contenthash" => (string)$record->contenthash,
            "status" => (string)$record->status,
            "aiused" => (bool)$record->aiused,
            "timecreated" => (int)$record->timecreated,
            "counts" => $counts,
            "severity_filter" => $severity,
            "total_filtered" => $total,
            "limit" => $limit,
            "offset" => $offset,
            "has_more" => $offset + $limit < $total,
            "findings" => array_slice($selected, $offset, $limit),
        ];
    }

    /**
     * Find the latest persisted matching run after a confirmed audit operation.
     *
     * @param int $courseid Course ID.
     * @param int $userid Initiating user.
     * @param string $mode Audit mode.
     * @param int $sectionid Course section identifier.
     * @param string $contenthash Content hash returned by the audit manager.
     * @param string $status Result status.
     * @return int|null
     */
    public function latest_matching(
        int $courseid, int $userid, string $mode, int $sectionid, string $contenthash, string $status
    ): ?int {
        global $DB;
        $records = $DB->get_records("local_courseaudit_run", [
            "courseid" => $courseid,
            "userid" => $userid,
            "mode" => $mode,
            "sectionid" => $sectionid,
            "contenthash" => $contenthash,
            "status" => $status,
        ], "timecreated DESC, id DESC", "id", 0, 1);
        $record = $records ? reset($records) : false;
        return $record ? (int)$record->id : null;
    }
}
