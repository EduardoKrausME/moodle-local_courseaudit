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
 * English language strings for local_courseaudit.
 *
 * @package   local_courseaudit
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['aiunavailable'] = 'Deterministic checks completed, but AI analysis is unavailable: {$a}';
$string['allsections'] = 'All sections';
$string['auditcourse'] = 'Audit course';
$string['auditfailed'] = 'The course audit could not be completed. Check Moodle debugging logs for technical details.';
$string['bridgeerror:credits'] = 'The tenant or user does not have enough AI Bridge credits.';
$string['bridgeerror:failed'] = 'AI Bridge could not complete the request.';
$string['bridgeerror:invalidjson'] = 'The AI provider returned a response that was not valid structured JSON.';
$string['bridgeerror:nopermission'] = 'The current user is not allowed to use AI Bridge.';
$string['bridgeerror:noroute'] = 'No AI Bridge route is configured for the course audit purpose and the user AI role.';
$string['bridgeerror:notenant'] = 'No enabled AI Bridge tenant is available for this user, or AI access is disabled for the user.';
$string['bridgeerror:notinstalled'] = 'local_ai_bridge is not installed or its API class is unavailable.';
$string['bridgeerror:providers'] = 'All configured AI providers/routes failed or are unavailable.';
$string['bridgeerror:purpose'] = 'The required AI Bridge purpose “courseaudit-analysis” is missing or disabled.';
$string['cachedresult'] = 'This result was reused from a previous completed audit because the relevant course content did not change.';
$string['category:accessibility'] = 'Basic accessibility';
$string['category:completion'] = 'Completion';
$string['category:content'] = 'Content';
$string['category:dates'] = 'Dates';
$string['category:grading'] = 'Assessment';
$string['category:links'] = 'Links';
$string['category:pedagogy'] = 'Pedagogical coherence';
$string['category:recommendations'] = 'Recommendations';
$string['category:structure'] = 'Structure';
$string['courseaudit:audit'] = 'Audit course quality';
$string['editrelated'] = 'Edit related item';
$string['evidence'] = 'Evidence';
$string['finding:brokenavailability:description'] = 'The availability condition references course module ID(s) that no longer exist: {$a}.';
$string['finding:brokenavailability:title'] = 'Availability references missing activity';
$string['finding:brokenlink:description'] = 'The internal Moodle URL points to an activity or course record that does not exist: {$a}.';
$string['finding:brokenlink:title'] = 'Broken internal Moodle link';
$string['finding:completiondisabled:description'] = 'Course completion tracking is disabled, so the course has no Moodle-managed completion path to audit.';
$string['finding:completiondisabled:title'] = 'Course completion is disabled';
$string['finding:coursedates:description'] = 'The configured course end date is earlier than its start date.';
$string['finding:coursedates:title'] = 'Course end date precedes start date';
$string['finding:cutoffbeforeduedate:description'] = '“{$a}” has a cut-off date earlier than its due date.';
$string['finding:cutoffbeforeduedate:title'] = 'Cut-off date precedes due date';
$string['finding:dateorder:description'] = 'In “{$a->activity}”, the closing/due date ({$a->to}) is earlier than the corresponding opening/start date ({$a->from}).';
$string['finding:dateorder:title'] = 'Activity dates are out of order';
$string['finding:duplicatecontent:description'] = 'Normalized content is identical in: {$a}.';
$string['finding:duplicatecontent:title'] = 'Identical content appears in multiple activities';
$string['finding:emptysection:description'] = 'The section “{$a}” contains no course modules.';
$string['finding:emptysection:title'] = 'Empty section';
$string['finding:headingjump:description'] = 'The content jumps from heading level H{$a->from} to H{$a->to}. Review whether the heading hierarchy represents the document structure.';
$string['finding:headingjump:title'] = 'Heading hierarchy skips a level';
$string['finding:hiddenactivity:description'] = '“{$a}” is hidden from learners. This may be intentional, but it is worth reviewing before course release.';
$string['finding:hiddenactivity:title'] = 'Hidden activity or resource';
$string['finding:iframenotitle:description'] = 'An embedded iframe has no meaningful title attribute.';
$string['finding:iframenotitle:title'] = 'Iframe without title';
$string['finding:imgnoalt:description'] = 'An image is missing the alt attribute. Decorative images should use alt=""; meaningful images need an appropriate text alternative.';
$string['finding:imgnoalt:title'] = 'Image without alt attribute';
$string['finding:invalidavailability:activity'] = 'The availability JSON for activity “{$a}” is invalid.';
$string['finding:invalidavailability:section'] = 'The availability JSON for section “{$a}” is invalid.';
$string['finding:invalidavailability:title'] = 'Invalid availability configuration';
$string['finding:invalidurl:description'] = 'The authored content contains a URL that is not a valid HTTP/HTTPS URL or supported local relative URL: {$a}.';
$string['finding:invalidurl:title'] = 'Apparently invalid URL';
$string['finding:missinggradeitem:description'] = '“{$a}” has a configured grade or scale value but no matching Moodle grade item was found.';
$string['finding:missinggradeitem:title'] = 'Graded activity has no grade item';
$string['finding:nocompletion:description'] = '“{$a}” is visible but does not use activity completion tracking.';
$string['finding:nocompletion:title'] = 'Activity has no completion rule';
$string['finding:nocoursecriteria:description'] = 'Completion tracking is enabled for the course, but no course completion criterion is configured.';
$string['finding:nocoursecriteria:title'] = 'No course completion criteria';
$string['finding:nodescription:description'] = '“{$a}” has no introduction/description. For this activity type, review whether learners receive enough instructions and context.';
$string['finding:nodescription:title'] = 'Activity has no description';
$string['finding:vaguelink:description'] = 'The link text “{$a}” does not clearly describe its destination when read outside surrounding context.';
$string['finding:vaguelink:title'] = 'Link text is vague';
$string['forcerun'] = 'Re-run even when course content did not change';
$string['forcerun_help'] = 'By default, a completed audit is reused when the course, mode and selected section produce the same content hash. Enable this option to force all checks and, when applicable, a new AI request.';
$string['intro'] = 'Checks objective Moodle configuration first and uses AI only for semantic and pedagogical interpretation. No correction is applied automatically.';
$string['invalidmode'] = 'Invalid audit mode.';
$string['invalidsection'] = 'The selected section does not belong to this course.';
$string['mode'] = 'Audit mode';
$string['modecontentai'] = 'Content and AI analysis only';
$string['modefull'] = 'Complete audit';
$string['modestructure'] = 'Structure and deterministic rules only';
$string['nofindings'] = 'No findings were produced for the selected audit scope.';
$string['origin'] = 'Origin';
$string['origin:ai'] = 'AI analysis';
$string['origin:rule'] = 'Moodle rule';
$string['pluginname'] = 'Course Audit';
$string['privacy:metadata:runs'] = 'Course audit runs cached to avoid repeating identical deterministic and AI work.';
$string['privacy:metadata:runs:aiused'] = 'Whether the audit included a successful AI analysis.';
$string['privacy:metadata:runs:contenthash'] = 'A one-way hash of the relevant normalized course snapshot used to detect unchanged audits.';
$string['privacy:metadata:runs:courseid'] = 'The course that was audited.';
$string['privacy:metadata:runs:findings'] = 'Structured findings only; raw prompts and raw model responses are not stored.';
$string['privacy:metadata:runs:mode'] = 'The selected audit mode.';
$string['privacy:metadata:runs:sectionid'] = 'The selected section scope, or zero for the whole course.';
$string['privacy:metadata:runs:status'] = 'Whether the audit completed fully or only partially.';
$string['privacy:metadata:runs:timecreated'] = 'When the audit was run.';
$string['privacy:metadata:runs:userid'] = 'The user who initiated the audit.';
$string['privacy:path'] = 'Course audit runs';
$string['related'] = 'Related item';
$string['runaudit'] = 'Run audit';
$string['sectionfilter'] = 'Section';
$string['sectionzero'] = 'General section';
$string['severity:ai_insight'] = 'AI INSIGHT';
$string['severity:error'] = 'ERROR';
$string['severity:suggestion'] = 'SUGGESTION';
$string['severity:warning'] = 'WARNING';
$string['suggestion'] = 'Suggestion';
$string['summaryai'] = 'AI insights';
$string['summaryerrors'] = 'errors';
$string['summarysuggestions'] = 'suggestions';
$string['summarywarnings'] = 'warnings';
