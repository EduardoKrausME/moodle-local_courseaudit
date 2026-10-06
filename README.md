# Moodle Course Audit (`local_courseaudit`)

`local_courseaudit` is a Moodle local plugin that works as a course linter: objective problems are detected by
deterministic PHP rules, while semantic and pedagogical interpretation is delegated to `local_ai_bridge` only after the
local checks are complete.

The plugin never modifies a course automatically. Every finding is advisory or diagnostic and any corrective action
remains a human decision.

## Permissions

The plugin defines:

```text
local/courseaudit:audit
```

It is allowed by default for the `editingteacher` and `manager` archetypes at course context. The “Audit course” entry
is only added to course navigation when the current user has this capability.

## Audit modes

- **Complete audit**: deterministic PHP rules first, then semantic AI analysis.
- **Structure only**: deterministic rules only; no AI request and therefore no AI credits consumed.
- **Content/AI only**: deterministic content, basic accessibility and link checks are still executed locally, followed
  by semantic AI analysis; structural/date/completion/grading rules are skipped.
- **Section filter**: limits the snapshot and report to a selected section.
- **Force re-run**: bypasses reuse of a previously completed audit with the same content hash.

## Deterministic-first architecture

The plugin deliberately uses PHP whenever Moodle data can answer the question reliably. Current deterministic checks
include:

- empty sections;
- hidden activities/resources;
- invalid availability JSON;
- availability conditions referring to missing course modules;
- course start/end inconsistencies;
- activity open/close, submission/due, submission/assessment window inconsistencies;
- cut-off date before due date;
- completion disabled, missing course completion criteria, and visible activities without completion tracking;
- graded activities with no corresponding grade item;
- activities/resources without an introduction where an introduction is normally expected;
- identical normalized authored content by deterministic hash;
- malformed or unhandled URLs;
- internal Moodle links pointing to missing activity/course records;
- basic authored HTML accessibility checks such as missing image `alt`, missing iframe `title`, vague link labels, and
  heading hierarchy jumps.

No outbound request is made while checking authored URLs. This avoids turning the linter into an SSRF primitive and
avoids reports that change merely because a remote site is temporarily slow.

## AI analysis

Only the minimum normalized course context needed for semantic analysis is sent to `local_ai_bridge`. The AI prompt asks
for valid JSON only and focuses on questions that deterministic Moodle data cannot settle safely, including:

- pedagogical coherence and sequence;
- unclear instructions;
- semantic repetition rather than literal duplication;
- apparent alignment between course objectives/context and activities;
- activities that appear weakly related to the learning path;
- likely content gaps;
- terminology inconsistency;
- workload concentrated in a section;
- clarity improvements.

AI findings may be `WARNING`, `SUGGESTION`, or `AI_INSIGHT`. AI output is never allowed to create an objective `ERROR`;
if a model returns `error`, the parser downgrades it to `WARNING`. Objective errors belong to deterministic rules.

The response is parsed with `JSON_THROW_ON_ERROR`, validated against the expected structure, limited to known
fields/categories, converted to plain text, and then rendered by Mustache. Model-provided HTML is never rendered
directly.

## AI Bridge failure handling

The bridge adapter handles these conditions without breaking the deterministic report:

- bridge API unavailable;
- user without `local/ai_bridge:use`;
- no enabled tenant or user disabled in the tenant;
- `courseaudit-analysis` missing or disabled;
- no route for the purpose/user AI role;
- tenant or user credits exhausted;
- provider/route failures;
- invalid JSON returned by the model.

When AI fails during a complete audit, deterministic findings are still shown and the run is stored as `partial`, so it
is not reused as a successful cached result on the next attempt.

## Privacy and data sent to AI

The snapshot intentionally contains course structure/content, not learner records. User lists, grades, submissions,
emails, names, passwords, tokens, cookies, session IDs, and other student data are not collected for AI analysis.

As an additional defense, text sent to AI is normalized and common email/token/secret patterns are redacted. The payload
also has per-field and total size limits.

The plugin does not persist raw prompts or raw model responses. Only structured findings may be stored
in `local_courseaudit_run` so a completed result can be reused when the content hash has not changed. Because each
persisted run records the initiating Moodle user ID, the plugin implements the Moodle Privacy API, including user-list
deletion handling.

## Cache

A SHA-256 content hash is generated from the audit-relevant snapshot after volatile edit URLs are removed. Completed
reports are reused only for the same:

```text
course + user + mode + section + content hash
```

This avoids unnecessary repeated AI cost while preventing one auditor's cached record from becoming another user's
personal audit history. Partial AI failures are not reused as successful cache entries.

## Report

Findings are grouped into:

- Structure;
- Dates;
- Completion;
- Assessment;
- Content;
- Pedagogical coherence;
- Basic accessibility;
- Links;
- Recommendations.

Each finding records severity, title, plain-text description, origin (`Moodle rule` or `AI analysis`), related item,
evidence/suggestion where applicable, and a direct Moodle edit URL when the finding belongs to a concrete
section/activity.

## Activity-level AI analysis

While editing a course, Course Audit adds an **Analyze with AI** control beside supported activities. It uses the same `courseaudit-analysis` AI Bridge purpose and stores content-hash-aware history for each activity.

The review covers spelling and clarity, alignment between section/title/content, pedagogical suitability, practical recommendations, and an explicit predominant Bloom level. Teachers can reopen the latest result or run a new analysis after changing the activity.

Course Audit does not extract assignment submissions, grades, forum posts, private messages, learner names or other learner records. It does analyze content published as part of the activity itself; in collaborative activities such as Wiki or Glossary, published pages or approved entries can therefore be included. During upgrade, existing activity-analysis history from `local_geniai` is migrated to `local_courseaudit_analysis` when the old table is available.
