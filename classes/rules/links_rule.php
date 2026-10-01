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
 * Deterministic URL and internal link checks.
 *
 * This rule intentionally performs no outbound HTTP requests, avoiding SSRF and unstable audits.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class links_rule extends base_rule {

    public function run(array $snapshot): array {
        global $CFG, $DB;

        $findings = [];
        $checkedcmids = [];
        $checkedcourseids = [];

        foreach ($snapshot['sections'] ?? [] as $section) {
            foreach ($section['modules'] as $module) {
                foreach ($module['urls'] ?? [] as $url) {
                    $url = trim((string)$url);
                    if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:')) {
                        continue;
                    }

                    $absolute = $this->absolute_url($url, (string)$CFG->wwwroot);
                    if ($absolute === null) {
                        $findings[] = $this->finding(
                            finding::WARNING,
                            'links',
                            'finding:invalidurl:title',
                            'finding:invalidurl:description',
                            $url,
                            (string)$module['name'],
                            (string)$module['editurl']
                        );
                        continue;
                    }

                    if (!$this->is_same_site($absolute, (string)$CFG->wwwroot)) {
                        continue;
                    }

                    $parts = parse_url($absolute);
                    if (!$parts || empty($parts['path'])) {
                        continue;
                    }
                    parse_str((string)($parts['query'] ?? ''), $query);

                    if (preg_match('~/mod/[^/]+/view\.php$~', $parts['path']) && !empty($query['id']) && is_numeric($query['id'])) {
                        $cmid = (int)$query['id'];
                        if (!array_key_exists($cmid, $checkedcmids)) {
                            $checkedcmids[$cmid] = $DB->record_exists('course_modules', ['id' => $cmid]);
                        }
                        if (!$checkedcmids[$cmid]) {
                            $findings[] = $this->broken_internal_link($module, $url);
                        }
                    }

                    if (str_ends_with($parts['path'], '/course/view.php') && !empty($query['id']) && is_numeric($query['id'])) {
                        $courseid = (int)$query['id'];
                        if (!array_key_exists($courseid, $checkedcourseids)) {
                            $checkedcourseids[$courseid] = $DB->record_exists('course', ['id' => $courseid]);
                        }
                        if (!$checkedcourseids[$courseid]) {
                            $findings[] = $this->broken_internal_link($module, $url);
                        }
                    }
                }
            }
        }

        return $findings;
    }

    /**
     * Convert relative same-site URLs to absolute and reject malformed schemes.
     *
     * @param string $url
     * @param string $wwwroot
     * @return string|null
     */
    private function absolute_url(string $url, string $wwwroot): ?string {
        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $url)) {
            if (!preg_match('~^https?://~i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
                return null;
            }
            return $url;
        }
        if (str_starts_with($url, '//')) {
            $candidate = 'https:' . $url;
            return filter_var($candidate, FILTER_VALIDATE_URL) === false ? null : $candidate;
        }
        if (str_starts_with($url, '/')) {
            return rtrim($wwwroot, '/') . $url;
        }
        if (preg_match('/[\x00-\x20]/u', $url)) {
            return null;
        }
        return rtrim($wwwroot, '/') . '/' . ltrim($url, './');
    }

    private function is_same_site(string $url, string $wwwroot): bool {
        $host = parse_url($url, PHP_URL_HOST);
        $sitehost = parse_url($wwwroot, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);
        $siteport = parse_url($wwwroot, PHP_URL_PORT);
        return $host !== null && $host === $sitehost && $port === $siteport;
    }

    private function broken_internal_link(array $module, string $url): finding {
        return $this->finding(
            finding::ERROR,
            'links',
            'finding:brokenlink:title',
            'finding:brokenlink:description',
            $url,
            (string)$module['name'],
            (string)$module['editurl']
        );
    }
}
