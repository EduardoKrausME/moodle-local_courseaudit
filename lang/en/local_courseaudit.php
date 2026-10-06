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

$string['analysis_ai_block'] = 'Course Audit AI analysis';
$string['analysis_close'] = 'Close';
$string['analysis_error'] = 'Could not analyze this activity.';
$string['analysis_excluded_plugins'] = 'Modules excluded from activity analysis';
$string['analysis_excluded_plugins_desc'] = 'The selected modules will not display activity analysis controls and will be excluded from activity-by-activity course analysis.';
$string['analysis_last'] = 'Last analysis';
$string['analysis_latest'] = 'Latest analysis';
$string['analysis_model_warning'] = 'This analysis used a mini/nano model. For deeper analysis, configure the courseaudit-analysis route in AI Bridge with a larger model.';
$string['analysis_no_content'] = 'No analysis content was returned.';
$string['analysis_not_supported'] = 'This activity type is not available for Course Audit analysis.';
$string['analysis_print'] = 'Print';
$string['analysis_print_analysis'] = 'Print analysis';
$string['analysis_print_popup_blocked'] = 'The browser blocked the print tab. Allow pop-ups and try again.';
$string['analysis_reanalyze'] = 'Analyze again';
$string['analysis_recommendations'] = 'Recommendations';
$string['analysis_result'] = 'Activity analysis';
$string['analysis_status_insufficient'] = 'Insufficient';
$string['analysis_status_needs_review'] = 'Needs review';
$string['analysis_status_ok'] = 'OK';
$string['analysis_status_ok_minor'] = 'OK with minor adjustments';
$string['analyze_activity'] = 'Analyze with AI';
$string['analyze_course'] = 'Analyze course activities with AI';
$string['analyzing_activity'] = 'Analyzing spelling, pedagogical coherence and Bloom taxonomy...';
$string['analyzing_course'] = 'Analyzing course activities...';
$string['prompt_activity_focus_alignment'] = 'prioritize coherence between course, section, title, and activity content.';
$string['prompt_activity_focus_bloom'] = 'prioritize Bloom taxonomy and the cognitive depth of the proposal.';
$string['prompt_activity_focus_full'] = 'complete activity analysis.';
$string['prompt_activity_focus_pedagogy'] = 'prioritize pedagogical adequacy, student instructions, and learning quality.';
$string['prompt_activity_focus_spelling'] = 'prioritize spelling, grammar, clarity, and instructional tone.';
$string['prompt_activity_schema_bloom_level'] = 'remember | understand | apply | analyze | evaluate | create';
$string['prompt_activity_schema_diagnosis'] = 'Short summary of the general diagnosis.';
$string['prompt_activity_schema_recommendation_1'] = 'Practical action 1.';
$string['prompt_activity_schema_recommendation_2'] = 'Practical action 2.';
$string['prompt_activity_schema_status'] = 'OK | OK with minor adjustments | Needs review | Inadequate or insufficient';
$string['prompt_activity_schema_status_key'] = 'ok | ok_minor | needs_review | insufficient';
$string['prompt_activity_system'] = 'You are an expert in instructional design, text review, and Moodle.

Analyze an existing Moodle activity using only the supplied authored content. Never infer learner data.
Write the visible Markdown analysis in the current Moodle language: {$a->lang}.
Keep technical JSON field names and enum values in English.
If content is insufficient, say so clearly.

Criteria:
1. Spelling, grammar and textual clarity.
2. Coherence between activity title, course section and activity content.
3. Predominant Bloom level: remember, understand, apply, analyze, evaluate, create.
4. Pedagogical adequacy.
5. Practical improvement suggestions.

Additional focus: {$a->focus}

Return visible Markdown with diagnosis, spelling/clarity, section coherence, Bloom taxonomy, improvements and final opinion.
The final classification must be exactly one of: OK, OK with minor adjustments, Needs review, Inadequate or insufficient.
Requested analysis type: {$a->analysis}';
$string['prompt_activity_user'] = 'Analyze the Moodle activity below.

{$a}';
$string['privacy:metadata:analysis'] = 'Activity-level pedagogical analyses stored as Course Audit history.';
$string['privacy:metadata:analysis:courseid'] = 'The course containing the analyzed activity.';
$string['privacy:metadata:analysis:cmid'] = 'The analyzed course module.';
$string['privacy:metadata:analysis:userid'] = 'The user who requested the analysis.';
$string['privacy:metadata:analysis:contenthash'] = 'A hash used to reuse analysis when authored activity content did not change.';
$string['privacy:metadata:analysis:result'] = 'Structured status, Bloom level, recommendations and analysis text.';
$string['privacy:metadata:analysis:timecreated'] = 'When the activity analysis was created.';
$string['privacy:analysispath'] = 'Activity analyses';
