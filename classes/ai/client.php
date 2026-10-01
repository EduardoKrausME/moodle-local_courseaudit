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

namespace local_courseaudit\ai;

use JsonException;
use local_ai_bridge\api;
use local_courseaudit\local\finding;
use moodle_exception;
use required_capability_exception;
use Throwable;

/**
 * Thin adapter around local_ai_bridge.
 *
 * No provider-specific code, model settings or credentials belong here.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client {
    public const PURPOSE = 'courseaudit-analysis';

    /**
     * Ask AI Bridge for semantic findings.
     *
     * @param array $context
     * @return array{findings: finding[], error: string|null, used: bool}
     */
    public function analyse(array $context): array {
        if (!class_exists('\\local_ai_bridge\\api')) {
            return $this->failure(get_string('bridgeerror:notinstalled', 'local_courseaudit'));
        }

        $messages = $this->build_messages($context);
        try {
            $response = api::generate(self::PURPOSE, $messages);
            $parser = new response_parser();
            $findings = $parser->parse($response->text);
            return ['findings' => $findings, 'error' => null, 'used' => true];
        } catch (required_capability_exception $exception) {
            return $this->failure(get_string('bridgeerror:nopermission', 'local_courseaudit'));
        } catch (moodle_exception $exception) {
            return $this->failure($this->map_bridge_exception($exception));
        } catch (JsonException $exception) {
            return $this->failure(get_string('bridgeerror:invalidjson', 'local_courseaudit'));
        } catch (Throwable $exception) {
            return $this->failure(get_string('bridgeerror:failed', 'local_courseaudit'));
        }
    }

    /**
     * Build messages demanding JSON-only semantic analysis.
     *
     * @param array $context
     * @return array
     */
    public function build_messages(array $context): array {
        $schema = [
            'findings' => [[
                'type' => 'warning|suggestion|ai_insight',
                'category' => 'pedagogy|content|grading|structure|recommendations',
                'title' => 'short plain-text title',
                'description' => 'plain-text explanation',
                'evidence' => ['plain-text evidence grounded in the supplied course context'],
                'suggestion' => 'optional plain-text recommendation',
            ]],
        ];
        $instruction = implode("\n", [
            'Analyze only semantic and pedagogical aspects that cannot be reliably determined by PHP rules.',
            'Focus on pedagogical coherence, sequence, unclear instructions, semantic repetition, alignment between objectives and activities,',
            'activities that appear weakly related to objectives, meaningful gaps, terminology inconsistency, concentrated workload, and clarity.',
            'Do not claim that an interpretive pedagogical choice is objectively wrong. Never return type "error".',
            'Do not infer facts that are absent. Evidence must quote or identify supplied sections/activities, not invented content.',
            'Return valid JSON only, with no Markdown fences and no HTML.',
            'Required schema: ' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Course context: ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return [['role' => 'user', 'content' => $instruction]];
    }

    /**
     * Method failure.
     *
     * @param string $message Parameter message.
     * @return array Return value.
     */
    private function failure(string $message): array {
        return ['findings' => [], 'error' => $message, 'used' => false];
    }

    /**
     * Convert known bridge errors to actionable UI messages without leaking exception internals.
     *
     * @param moodle_exception $exception
     * @return string
     */
    private function map_bridge_exception(moodle_exception $exception): string {
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
