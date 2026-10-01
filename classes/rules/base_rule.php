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

namespace local_courseaudit\rules;

use local_courseaudit\local\finding;

/**
 * Shared helpers for deterministic rules.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class base_rule implements rule_interface {
    /**
     * Create a finding using plugin language strings.
     *
     * @param string $type
     * @param string $category
     * @param string $titlekey
     * @param string $descriptionkey
     * @param mixed $a
     * @param string $related
     * @param string $editurl
     * @return finding
     */
    protected function finding(
        string $type,
        string $category,
        string $titlekey,
        string $descriptionkey,
        mixed $a = null,
        string $related = '',
        string $editurl = ''
    ): finding {
        return new finding(
            $type,
            $category,
            get_string($titlekey, 'local_courseaudit'),
            get_string($descriptionkey, 'local_courseaudit', $a),
            finding::SOURCE_RULE,
            $related,
            $editurl
        );
    }

    /**
     * Find broken activity references in availability JSON.
     *
     * @param string $json
     * @param array $validcmids
     * @return array
     */
    protected function broken_availability_cmids(string $json, array $validcmids): array {
        if (trim($json) === '') {
            return [];
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [-1];
        }

        $references = [];
        $walk = static function (mixed $node) use (&$walk, &$references): void {
            if (!is_array($node)) {
                return;
            }
            if (($node['type'] ?? '') === 'completion' && isset($node['cm']) && is_numeric($node['cm'])) {
                $references[] = (int)$node['cm'];
            }
            foreach ($node as $value) {
                if (is_array($value)) {
                    $walk($value);
                }
            }
        };
        $walk($data);

        return array_values(array_filter(array_unique($references), static fn(int $id): bool => !in_array($id, $validcmids, true)));
    }
}
