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
 * Provider-neutral activity analysis response.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\ai;

/**
 * Provider-neutral response used by the activity analyzer.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_response {
    /** @var bool Whether generation succeeded. */
    public $success = false;

    /** @var string Generated text. */
    public $content = '';

    /** @var string Error message. */
    public $error = '';

    /** @var string Provider model. */
    public $model = '';

    /** @var int Input tokens. */
    public $prompttokens = 0;

    /** @var int Output tokens. */
    public $completiontokens = 0;

    /** @var int Total tokens. */
    public $totaltokens = 0;

    /** @var array Safe response metadata. */
    public $raw = [];

    /**
     * Build from AI Bridge response.
     *
     * @param \local_ai_bridge\bridge\response $bridge Bridge response.
     * @return self
     */
    public static function from_bridge(\local_ai_bridge\bridge\response $bridge): self {
        $response = new self();
        $response->success = true;
        $response->content = $bridge->text;
        $response->model = $bridge->model;
        $response->prompttokens = $bridge->inputtokens;
        $response->completiontokens = $bridge->outputtokens;
        $response->totaltokens = $bridge->totaltokens;
        $response->raw = [
            'model' => $bridge->model,
            'metadata' => $bridge->metadata,
        ];
        return $response;
    }

    /**
     * Build an error response.
     *
     * @param string $message Error message.
     * @return self
     */
    public static function error(string $message): self {
        $response = new self();
        $response->error = $message;
        return $response;
    }
}
