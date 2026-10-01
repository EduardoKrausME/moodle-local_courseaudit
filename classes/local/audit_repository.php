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

namespace local_courseaudit\local;

/**
 * Persistence/cache for structured audit findings.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_repository {
    /**
     * Find a completed cached audit for the same user and snapshot hash.
     *
     * @param int $courseid
     * @param int $userid
     * @param string $mode
     * @param int $sectionid
     * @param string $contenthash
     * @return array|null
     */
    public function find(int $courseid, int $userid, string $mode, int $sectionid, string $contenthash): ?array {
        global $DB;

        $records = $DB->get_records('local_courseaudit_run', [
            'courseid' => $courseid,
            'userid' => $userid,
            'mode' => $mode,
            'sectionid' => $sectionid,
            'contenthash' => $contenthash,
            'status' => 'complete',
        ], 'timecreated DESC, id DESC', '*', 0, 1);

        $record = $records ? reset($records) : false;
        if (!$record) {
            return null;
        }

        $decoded = json_decode($record->findingsjson, true);
        if (!is_array($decoded)) {
            return null;
        }

        $findings = [];
        foreach ($decoded as $row) {
            if (is_array($row)) {
                $findings[] = finding::from_array($row);
            }
        }

        return [
            'findings' => $findings,
            'aiused' => !empty($record->aiused),
            'timecreated' => (int)$record->timecreated,
        ];
    }

    /**
     * Store only normalized findings, never prompts or raw model responses.
     *
     * @param int $courseid
     * @param int $userid
     * @param string $mode
     * @param int $sectionid
     * @param string $contenthash
     * @param finding[] $findings
     * @param bool $aiused
     * @param string $status
     * @return int
     */
    public function save(
        int    $courseid,
        int    $userid,
        string $mode,
        int    $sectionid,
        string $contenthash,
        array  $findings,
        bool   $aiused,
        string $status
    ): int {
        global $DB;

        $now = time();
        $json = json_encode(
            array_map(static fn(finding $finding): array => $finding->to_array(), $findings),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false) {
            $json = '[]';
        }

        return (int)$DB->insert_record('local_courseaudit_run', (object)[
            'courseid' => $courseid,
            'userid' => $userid,
            'mode' => $mode,
            'sectionid' => $sectionid,
            'contenthash' => $contenthash,
            'findingsjson' => $json,
            'aiused' => $aiused ? 1 : 0,
            'status' => $status,
            'timecreated' => $now,
            'timemodified' => $now,
        ]);
    }
}
