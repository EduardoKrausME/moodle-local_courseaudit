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
 * AI Bridge adapter for activity-level pedagogical analysis.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_courseaudit\ai;

use local_ai_bridge\api;
use moodle_exception;
use required_capability_exception;
use Throwable;

/**
 * AI Bridge adapter for activity-level pedagogical analysis.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class activity_client {
    /**
     * Generate analysis text with a final machine-readable JSON block.
     *
     * @param array $messages Chat messages.
     * @param array $schema Expected JSON structure.
     * @return activity_response
     */
    public static function generate_json(array $messages, array $schema = []): activity_response {
        if (!class_exists('\\local_ai_bridge\\api')) {
            return activity_response::error(get_string('bridgeerror:notinstalled', 'local_courseaudit'));
        }

        if ($messages) {
            $last = count($messages) - 1;
            $schemajson = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $fence = str_repeat(chr(96), 3);
            $messages[$last]['content'] .= "\n\nReturn one final valid JSON block between {$fence}json and {$fence}.";
            $messages[$last]['content'] .= "\nDo not translate JSON field names or enum values.";
            if ($schemajson !== false) {
                $messages[$last]['content'] .= "\nExpected JSON structure: " . $schemajson;
            }
        }

        try {
            return activity_response::from_bridge(api::generate(client::PURPOSE, $messages));
        } catch (required_capability_exception $exception) {
            return activity_response::error(get_string('bridgeerror:nopermission', 'local_courseaudit'));
        } catch (moodle_exception $exception) {
            return activity_response::error(self::map_exception($exception));
        } catch (Throwable $exception) {
            return activity_response::error(get_string('bridgeerror:failed', 'local_courseaudit'));
        }
    }

    /**
     * Map bridge errors to Course Audit messages.
     *
     * @param moodle_exception $exception Bridge exception.
     * @return string
     */
    private static function map_exception(moodle_exception $exception): string {
        return match ($exception->errorcode) {
            'notenant', 'userdisabled' => get_string('bridgeerror:notenant', 'local_courseaudit'),
            'purposeunavailable' => get_string('bridgeerror:purpose', 'local_courseaudit'),
            'noroute' => get_string('bridgeerror:noroute', 'local_courseaudit'),
            'tenantcredits', 'usercredits' => get_string('bridgeerror:credits', 'local_courseaudit'),
            'allroutesfailed', 'bridgeunavailable', 'configdecrypt', 'hostnotallowed', 'invalidendpoint' =>
                get_string('bridgeerror:providers', 'local_courseaudit'),
            default => get_string('bridgeerror:failed', 'local_courseaudit'),
        };
    }
}
