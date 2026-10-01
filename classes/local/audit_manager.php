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

use local_courseaudit\ai\client as ai_client;
use local_courseaudit\rules\accessibility_rule;
use local_courseaudit\rules\completion_rule;
use local_courseaudit\rules\content_rule;
use local_courseaudit\rules\dates_rule;
use local_courseaudit\rules\grading_rule;
use local_courseaudit\rules\links_rule;
use local_courseaudit\rules\rule_interface;
use local_courseaudit\rules\structure_rule;
use invalid_parameter_exception;
use stdClass;

/**
 * Orchestrates deterministic-first course audits.
 *
 * @package local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class audit_manager {
    /** Supported audit modes. */
    public const MODES = ['full', 'structure', 'contentai'];

    /** @var rule_interface[] */
    private array $rules;

    /**
     * Constructor.
     *
     * @param rule_interface[]|null $rules
     */
    public function __construct(?array $rules = null) {
        $this->rules = $rules ?? [
            new structure_rule(),
            new dates_rule(),
            new completion_rule(),
            new grading_rule(),
            new content_rule(),
            new accessibility_rule(),
            new links_rule(),
        ];
    }

    /**
     * Run an audit or reuse a previous identical completed result.
     *
     * @param stdClass $course
     * @param int $userid
     * @param string $mode
     * @param int $sectionid
     * @param bool $force
     * @return array
     */
    public function run(stdClass $course, int $userid, string $mode, int $sectionid = 0, bool $force = false): array {
        if (!in_array($mode, self::MODES, true)) {
            throw new invalid_parameter_exception('Unsupported course audit mode.');
        }

        $snapshot = (new snapshot_builder())->build($course, $sectionid);
        $contenthash = (new snapshot_hasher())->hash($snapshot);
        $repository = new audit_repository();

        if (!$force) {
            $cached = $repository->find((int)$course->id, $userid, $mode, $sectionid, $contenthash);
            if ($cached !== null) {
                return $this->result($cached['findings'], true, null, (bool)$cached['aiused'], $contenthash);
            }
        }

        $findings = [];
        foreach ($this->rules_for_mode($mode) as $rule) {
            array_push($findings, ...$rule->run($snapshot));
        }

        $aierror = null;
        $aiused = false;
        if ($mode !== 'structure') {
            $context = (new context_builder())->build($snapshot);
            $airesult = (new ai_client())->analyse($context);
            array_push($findings, ...$airesult['findings']);
            $aierror = $airesult['error'];
            $aiused = (bool)$airesult['used'];
        }

        $status = $aierror === null ? 'complete' : ($mode === 'structure' ? 'complete' : 'partial');
        $repository->save(
            (int)$course->id,
            $userid,
            $mode,
            $sectionid,
            $contenthash,
            $findings,
            $aiused,
            $status
        );

        return $this->result($findings, false, $aierror, $aiused, $contenthash);
    }

    /**
     * Return deterministic rules applicable to an audit mode.
     *
     * Content/AI mode still runs locally decidable content checks. Skipping them and asking
     * the model instead would violate the deterministic-first design.
     *
     * @param string $mode
     * @return rule_interface[]
     */
    private function rules_for_mode(string $mode): array {
        if ($mode !== 'contentai') {
            return $this->rules;
        }

        return array_values(array_filter(
            $this->rules,
            static fn(rule_interface $rule): bool => $rule instanceof content_rule || $rule instanceof accessibility_rule || $rule instanceof links_rule
        ));
    }

    /**
     * Build result summary.
     *
     * @param finding[] $findings
     * @param bool $cached
     * @param string|null $aierror
     * @param bool $aiused
     * @param string $contenthash
     * @return array
     */
    private function result(array $findings, bool $cached, ?string $aierror, bool $aiused, string $contenthash): array {
        $stats = [
            finding::ERROR => 0,
            finding::WARNING => 0,
            finding::SUGGESTION => 0,
            finding::AI_INSIGHT => 0,
        ];
        foreach ($findings as $finding) {
            if (isset($stats[$finding->type])) {
                $stats[$finding->type]++;
            }
        }

        return [
            'findings' => $findings,
            'stats' => $stats,
            'cached' => $cached,
            'aierror' => $aierror,
            'aiused' => $aiused,
            'contenthash' => $contenthash,
        ];
    }
}
