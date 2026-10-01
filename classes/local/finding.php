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
 * Immutable structured finding.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class finding {
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const SUGGESTION = 'suggestion';
    public const AI_INSIGHT = 'ai_insight';

    public const SOURCE_RULE = 'rule';
    public const SOURCE_AI = 'ai';

    /**
     * Constructor.
     *
     * @param string $type
     * @param string $category
     * @param string $title
     * @param string $description
     * @param string $source
     * @param string $related
     * @param string $editurl
     * @param array $evidence
     * @param string $suggestion
     */
    public function __construct(
        public readonly string $type,
        public readonly string $category,
        public readonly string $title,
        public readonly string $description,
        public readonly string $source = self::SOURCE_RULE,
        public readonly string $related = '',
        public readonly string $editurl = '',
        public readonly array  $evidence = [],
        public readonly string $suggestion = ''
    ) {
    }

    /**
     * Convert finding to a persistence-safe array.
     *
     * @return array
     */
    public function to_array(): array {
        return [
            'type' => $this->type,
            'category' => $this->category,
            'title' => $this->title,
            'description' => $this->description,
            'source' => $this->source,
            'related' => $this->related,
            'editurl' => $this->editurl,
            'evidence' => array_values($this->evidence),
            'suggestion' => $this->suggestion,
        ];
    }

    /**
     * Restore a finding from stored structured data.
     *
     * @param array $data
     * @return self
     */
    public static function from_array(array $data): self {
        return new self(
            (string)($data['type'] ?? self::WARNING),
            (string)($data['category'] ?? 'recommendations'),
            (string)($data['title'] ?? ''),
            (string)($data['description'] ?? ''),
            (string)($data['source'] ?? self::SOURCE_RULE),
            (string)($data['related'] ?? ''),
            (string)($data['editurl'] ?? ''),
            is_array($data['evidence'] ?? null) ? $data['evidence'] : [],
            (string)($data['suggestion'] ?? '')
        );
    }
}
