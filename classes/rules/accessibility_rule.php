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

use DOMDocument;
use DOMElement;
use DOMXPath;
use local_courseaudit\local\finding;

/**
 * Basic deterministic accessibility checks on authored HTML.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class accessibility_rule extends base_rule {
    /** @inheritdoc */
    public function run(array $snapshot): array {
        $findings = [];
        foreach ($snapshot['sections'] ?? [] as $section) {
            foreach ($section['modules'] as $module) {
                $html = (string)$module['contenthtml'];
                if (trim($html) === '') {
                    continue;
                }
                foreach ($this->inspect($html) as $issue) {
                    $findings[] = $this->finding(
                        $issue['type'],
                        'accessibility',
                        $issue['title'],
                        $issue['description'],
                        $issue['a'] ?? null,
                        (string)$module['name'],
                        (string)$module['editurl']
                    );
                }
            }
        }
        return $findings;
    }

    /**
     * Inspect HTML locally; this is deliberately not an AI task.
     *
     * @param string $html
     * @return array
     */
    private function inspect(string $html): array {
        $issues = [];
        if (!class_exists(DOMDocument::class)) {
            return $issues;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if (!$loaded) {
            return $issues;
        }

        foreach ($dom->getElementsByTagName('img') as $image) {
            if (!$image instanceof DOMElement || !$image->hasAttribute('alt')) {
                $issues[] = [
                    'type' => finding::WARNING,
                    'title' => 'finding:imgnoalt:title',
                    'description' => 'finding:imgnoalt:description',
                ];
            }
        }

        foreach ($dom->getElementsByTagName('iframe') as $iframe) {
            if (!$iframe instanceof DOMElement || trim($iframe->getAttribute('title')) === '') {
                $issues[] = [
                    'type' => finding::WARNING,
                    'title' => 'finding:iframenotitle:title',
                    'description' => 'finding:iframenotitle:description',
                ];
            }
        }

        foreach ($dom->getElementsByTagName('a') as $anchor) {
            $text = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $anchor->textContent) ?? $anchor->textContent));
            if (in_array($text, ['click here', 'clique aqui', 'aqui', 'more', 'saiba mais'], true)) {
                $issues[] = [
                    'type' => finding::WARNING,
                    'title' => 'finding:vaguelink:title',
                    'description' => 'finding:vaguelink:description',
                    'a' => $text,
                ];
            }
        }

        $xpath = new DOMXPath($dom);
        $previouslevel = null;
        foreach ($xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6') ?: [] as $heading) {
            if (!$heading instanceof DOMElement) {
                continue;
            }
            $level = (int)substr(strtolower($heading->tagName), 1);
            if ($previouslevel !== null && $level > $previouslevel + 1) {
                $issues[] = [
                    'type' => finding::WARNING,
                    'title' => 'finding:headingjump:title',
                    'description' => 'finding:headingjump:description',
                    'a' => (object)['from' => $previouslevel, 'to' => $level],
                ];
                break;
            }
            $previouslevel = $level;
        }

        return $issues;
    }
}
