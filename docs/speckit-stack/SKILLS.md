# The 10 Skills — Deep Dive

> Canonical (English) source of truth. See `SKILLS.uk.md` for a Ukrainian translation.
>
> Each skill is defined in `.claude/skills/speckit-<name>/SKILL.md`. Every one of them shares a
> common envelope not repeated per-skill below (see "Shared envelope" at the end): a
> `$ARGUMENTS` user-input block, a **Pre-Execution Checks** step that looks for
> `.specify/extensions.yml` hooks, and a **Mandatory/Post-Execution Hooks** step at the end
> that does the same for `after_*` hooks. What's documented here is each skill's *distinct*
> logic.

## 0. `speckit-constitution`

**One-liner**: Create or update the project constitution from interactive or provided
principle inputs, ensuring all dependent templates stay in sync.

**When**: Once at project setup, and again any time governing principles change. Not part of
the per-feature pipeline.

**Scope guard**: This is the one skill with an explicit anti-scope-creep rule baked in — if the
user's input mixes governance content with an unrelated ask ("also implement X while you're at
it"), the skill must *not* execute the unrelated part. It classifies every part of the input as
constitution-content vs. deferred-intent, and lists deferred intents under a `Next Actions`
section at the end (suggesting the right follow-up command, e.g. `/speckit-specify`, without
invoking it).

**Algorithm**:
1. Load `.specify/memory/constitution.md` (a template with `[ALL_CAPS]` placeholders the first
   time it's run).
2. Fill every placeholder from user input, falling back to repo context (README, prior
   constitution text) when the user didn't specify something. Dates must be ISO
   (`YYYY-MM-DD`); unknown dates become `TODO(FIELD_NAME): explanation` rather than being
   guessed.
3. Decide the semver bump: MAJOR for incompatible principle removal/redefinition, MINOR for a
   new/materially-expanded principle, PATCH for wording-only changes. If ambiguous, the skill
   must state its reasoning before finalizing — it does not pick silently.
4. **Propagation checklist** — re-read and reconcile against the new principles:
   - `.specify/templates/plan-template.md`'s Constitution Check gate section
   - `.specify/templates/spec-template.md`'s scope/requirements sections
   - `.specify/templates/tasks-template.md`'s task categorization
   - every other `speckit-*` skill file, to catch stale agent-specific references
   - any top-level runtime docs (README.md, quickstart docs)
5. Prepend a **Sync Impact Report** (HTML comment) to the file: version old→new, principles
   added/modified/removed, which templates were checked (✅/⚠), remaining TODOs.
6. Validate: no unexplained bracket tokens remain, dates are ISO, principles are
   declarative/testable (not "should" without a MUST/SHOULD rationale).
7. Overwrite `.specify/memory/constitution.md` — this skill **never** creates a new file, only
   edits the one canonical constitution.
8. Report: new version + bump rationale, files flagged for manual follow-up, a suggested commit
   message, and the `Next Actions` block for deferred intents (if any).

**Hard constraint**: must not create/modify/delete application source files, routes,
components, tests, or deployment files — ever, regardless of what's in the user's input.

## 1. `speckit-specify`

**One-liner**: Create or update the feature specification from a natural language feature
description.

**When**: Starting a new feature. First command of the per-feature pipeline.

**Algorithm**:
1. Derive a **2–4 word short name** from the description (action-noun style, preserving
   acronyms: "Implement OAuth2 integration for the API" → `oauth2-api-integration`).
2. If a `before_specify` extension hook ran (e.g. a git extension), note the
   `BRANCH_NAME`/`FEATURE_NUM` it returned — but the **spec directory name is independent of
   the git branch name**, this command never dictates or requires a particular branch.
3. Resolve `SPECIFY_FEATURE_DIRECTORY`: explicit override wins; otherwise auto-generate under
   `specs/` using `feature_numbering` from `init-options.json` (`sequential` 3-digit prefix by
   scanning existing dirs, or `timestamp` `YYYYMMDD-HHMMSS`). `mkdir`, copy the resolved
   `spec-template.md` to `spec.md`, persist the path to `.specify/feature.json`.
   **Exactly one feature per invocation.**
4. Load the spec template + (if present) the constitution, then fill the template:
   - Extract actors/actions/data/constraints from the description.
   - For unclear points, make an informed default guess; only emit
     `[NEEDS CLARIFICATION: ...]` when the choice meaningfully changes scope/UX and no
     reasonable default exists. **Hard cap: 3 markers**, prioritized scope > security/privacy >
     UX > technical detail.
   - Fill User Scenarios (prioritized, independently-testable user stories with Given/When/Then
     acceptance scenarios), Functional Requirements (`FR-###`, each testable), Success Criteria
     (`SC-###`, measurable **and technology-agnostic** — "Users complete checkout in under 3
     minutes," never "API responds in <200ms"), Key Entities, Assumptions.
5. **Quality-gate itself**: generate `checklists/requirements.md` (Content Quality /
   Requirement Completeness / Feature Readiness checkboxes) and self-score the spec against
   it, up to 3 repair iterations. Any remaining `[NEEDS CLARIFICATION]` markers get surfaced to
   the user as a **numbered, max-3, table-formatted multiple-choice question set** (with an
   explicit "Custom" row) — answered all together, then woven back into the spec, then the
   checklist is re-run.
6. Report: feature directory, spec file path, checklist pass summary, and whether the next
   step should be `/speckit-clarify` or straight to `/speckit-plan`.

**Note**: branch creation is explicitly *not* this command's job (it's the git extension's, via
a hook) — the spec directory and file are always created here regardless of hooks.

## 2. `speckit-clarify`

**One-liner**: Identify underspecified areas in the current feature spec by asking up to 5
highly targeted clarification questions and encoding answers back into the spec.

**When**: Right after specify, before plan. Expected (not strictly required) to run before
`/speckit-plan` — skipping is allowed for exploratory spikes but the skill must warn about
increased rework risk if so.

**Algorithm**:
1. `check-prerequisites.sh --json --paths-only` once to get `FEATURE_DIR`/`FEATURE_SPEC`.
2. Scan the spec against an 11-category **ambiguity taxonomy** (Functional Scope, Domain &
   Data Model, Interaction & UX Flow, Non-Functional Quality, Integration & External
   Dependencies, Edge Cases & Failure Handling, Constraints & Tradeoffs, Terminology,
   Completion Signals, Misc/Placeholders), marking each Clear/Partial/Missing. This map is
   internal — never dumped raw unless zero questions will be asked.
3. Build a **prioritized queue of ≤5 candidate questions** from Partial/Missing categories,
   each answerable as either a 2–5-option multiple-choice or a ≤5-word short answer. Exclude
   anything that wouldn't change architecture/data-model/tests/UX/ops/compliance.
4. **Ask one question at a time.** For multiple-choice: compute and lead with a
   `**Recommended:** Option X — reasoning` line, then a markdown table of options (incl. a
   free-form "Short" row), then accept "yes"/"recommended" as agreement with the
   recommendation. For short-answer: lead with `**Suggested:** ... — reasoning`. Stop early if
   remaining ambiguities resolve themselves, the user signals "done", or 5 questions are asked
   — whichever comes first. Never reveal queued-but-unasked questions.
5. **After each accepted answer** (not batched at the end): append
   `- Q: <question> → A: <answer>` under a `## Clarifications` / `### Session YYYY-MM-DD`
   section, then immediately fold the answer into the *most relevant* existing section
   (Functional Requirements / User Stories / Data Model / Success Criteria / Edge Cases /
   terminology normalization — replacing contradicted text rather than appending beside it),
   then **save the file immediately** (atomic per-answer writes, not one write at the end, to
   survive a dropped session).
6. Re-validate `checklists/requirements.md` if present: recompute each checkbox against the
   *updated* spec, flip only the ones whose state actually changed, and report **newly
   passing** vs. **regressions** (checked → unchecked) vs. **still unchecked**.
7. Report: questions asked/answered, sections touched, checklist before/after counts, a
   coverage-summary table (Resolved / Deferred / Clear / Outstanding per category), and a
   recommendation on whether to proceed to `/speckit-plan` or clarify again later.

**Hard constraints**: never more than 5 total accepted questions; if the spec file is missing,
tell the user to run `/speckit-specify` — this skill never creates a spec.

## 3. `speckit-plan`

**One-liner**: Execute the implementation planning workflow using the plan template to generate
design artifacts.

**When**: Once the spec is clear (ideally post-clarify).

**Algorithm**:
1. `setup-plan.sh --json` → `FEATURE_SPEC`, `IMPL_PLAN`, `SPECS_DIR`, `BRANCH` (idempotently
   copies `plan-template.md` to `plan.md` if not already present).
2. Load the spec + constitution.
3. Fill the plan template's **Technical Context** (language, dependencies, storage, testing,
   platform, performance/scale — mark anything unknown as `NEEDS CLARIFICATION`), then the
   **Constitution Check** gate against the project's actual principles (ERROR on an unjustified
   violation — justification, if any, goes in the **Complexity Tracking** table, which is the
   *only* way to proceed past a gate failure other than simplifying the approach).
4. **Phase 0 — Research**: for every `NEEDS CLARIFICATION` and every notable
   dependency/integration, spin up a research task ("Research X for Y", "Find best practices
   for Z in domain W"), consolidate into `research.md` as
   Decision / Rationale / Alternatives-considered triples. Output must leave **zero**
   unresolved `NEEDS CLARIFICATION` markers in Technical Context.
5. **Phase 1 — Design & Contracts** (prerequisite: research.md complete):
   - `data-model.md`: entities + fields + relationships + validation rules + state
     transitions, extracted from the spec.
   - `contracts/`: one file per externally-exposed interface (REST endpoints, CLI command
     schemas, public library APIs, UI contracts — whatever the project type exposes).
     **Skipped entirely** for purely-internal projects (build scripts, one-off tools).
   - `quickstart.md`: a *runnable validation guide* (prerequisites, setup/run commands,
     expected outcomes) that **must not** contain full implementation code, model/service
     bodies, migrations, or test suites — that's `tasks.md`'s and `speckit-implement`'s job.
   - Re-run the Constitution Check post-design (a design can introduce violations the initial
     Technical Context didn't have).
6. Report: branch, `plan.md` path, and the generated artifact list. Nothing proceeds past a
   gate failure or an unresolved clarification — this command errors rather than guessing.

## 4. `speckit-tasks`

**One-liner**: Generate an actionable, dependency-ordered `tasks.md` for the feature based on
available design artifacts.

**When**: After plan.md exists.

**Algorithm**:
1. `setup-tasks.sh --json` → `FEATURE_DIR`, `TASKS_TEMPLATE` (resolved through the override
   stack — hard error if no `tasks-template` is resolvable anywhere), `AVAILABLE_DOCS`.
2. **Required**: `plan.md` (tech stack, structure) + `spec.md` (prioritized user stories).
   **Optional, used if present**: `data-model.md` (entities), `contracts/` (interfaces),
   `research.md` (setup-task decisions), `constitution.md`.
3. **Primary organizing axis is the user story**, not the architectural layer. For each
   `P1`/`P2`/`P3`… story from spec.md: collect the models/services/endpoints/UI it needs
   (mapped from data-model.md/contracts/) into one self-contained phase. Tests are generated
   **only if explicitly requested** (TDD ask, or the spec calls for tests) — they are not a
   default.
4. **Checklist format is non-negotiable** per task line:
   `- [ ] T### [P?] [USx?] Description with exact file path`
   - `T###` sequential, execution order.
   - `[P]` present only if the task touches different files with no dependency on an
     incomplete task.
   - `[USx]` present **only** inside a user-story phase (never in Setup/Foundational/Polish).
   - A file path is mandatory in the description — "Create User model" without a path is
     invalid output.
5. **Phase structure**: `Phase 1: Setup` → `Phase 2: Foundational` (blocking, must finish
   before *any* story starts) → `Phase 3+`: one phase per user story in priority order
   (within each: Tests[if requested] → Models → Services → Endpoints → Integration) →
   `Final Phase: Polish & Cross-Cutting`.
6. Emit a dependency section (phase ordering + per-story independence notes), parallel-execution
   examples per story, and an "Implementation Strategy" block recommending MVP-first
   (Setup+Foundational+Story-1-only) then incremental delivery of further stories.
7. Report: total task count, per-story breakdown, parallel opportunities, suggested MVP scope
   (typically just User Story 1), and an explicit format-validation confirmation.

**Design intent**: each task must be specific enough that an LLM can execute it with zero
*additional* context beyond the task line and the referenced design docs.

## 5. `speckit-checklist`

**One-liner**: Generate a custom checklist for the current feature based on user requirements.

**When**: Any time after a spec exists; repeatable; different domains get different files.

**The core concept — "unit tests for English"**: a checklist item validates the *quality of a
requirement's wording*, **not** whether the implementation works. This is the single most
important and most-violated rule in this skill, so it's worth over-stating:

| ❌ Wrong (tests implementation) | ✅ Right (tests requirement quality) |
|---|---|
| "Verify landing page displays 3 episode cards" | "Are the exact number and layout of featured episodes specified? [Completeness]" |
| "Test hover states work on desktop" | "Are hover-state requirements consistently defined across all interactive elements? [Consistency]" |
| "Confirm logo click navigates home" | "Are navigation requirements clear for all clickable brand elements? [Clarity]" |

Forbidden verbs in any item: "Verify", "Test", "Confirm", "Check" + an implementation
behavior; forbidden phrases: "displays correctly", "works properly", "click", "navigate",
"render", "load", "execute". Required pattern: "Are/Is [requirement] defined/specified/
quantified/consistent/measurable for [scenario]?"

**Algorithm**:
1. `check-prerequisites.sh --json` for `FEATURE_DIR` + `AVAILABLE_DOCS`.
2. Ask **up to 3** dynamically-generated clarifying questions (never a pre-baked catalog) about
   scope/depth/audience, derived from signal extraction on the user's own phrasing — skip any
   question already answered by what the user typed. May extend to 2 more (5 total max) only
   if ≥2 scenario classes (Alternate/Exception/Recovery/Non-Functional) remain unclear after
   the first round, each extra question justified in one line.
3. Load spec/plan/tasks (only the portions relevant to the chosen focus areas — summarize
   rather than dumping full files).
4. **Generate the file**: `FEATURE_DIR/checklists/<domain>.md` (e.g. `ux.md`, `api.md`,
   `security.md`, `performance.md`). If it doesn't exist, start numbering at `CHK001`; if it
   does, **append** continuing from the last existing `CHK###` — existing content is never
   deleted or rewritten.
5. Categories: Requirement Completeness, Clarity, Consistency, Acceptance Criteria Quality,
   Scenario Coverage, Edge Case Coverage, Non-Functional Requirements, Dependencies &
   Assumptions, Ambiguities & Conflicts.
6. **Traceability**: ≥80% of items must cite either a spec section (`[Spec §FR-5]`) or a marker
   (`[Gap]`, `[Ambiguity]`, `[Conflict]`, `[Assumption]`). Soft cap of ~40 raw items before
   consolidation by risk/impact; >5 low-impact edge cases collapse into one item.
7. Report: full path, item count, new-file-vs-appended, focus areas/depth/audience chosen.

## 6. `speckit-analyze`

**One-liner**: Perform a non-destructive cross-artifact consistency and quality analysis across
`spec.md`, `plan.md`, and `tasks.md` after task generation.

**When**: After `speckit-tasks`, before `speckit-implement`. **Strictly read-only** — this
skill is the only one in the set that is contractually forbidden from writing any file; it
produces a chat report and, optionally on explicit user approval, *suggests* remediation edits
(but doesn't apply them in this invocation).

**Algorithm**:
1. `check-prerequisites.sh --json --require-tasks --include-tasks`, hard-abort naming the
   missing prerequisite command if `spec.md`/`plan.md`/`tasks.md` isn't all present.
2. Load progressively (not full-file dumps): Overview/FRs/SCs/UserStories/EdgeCases from spec;
   architecture/data-model-refs/phases/constraints from plan; IDs/descriptions/phase/`[P]`/
   file-paths from tasks; principle names + MUST/SHOULD statements from the constitution.
3. Build internal semantic models (never echoed raw): a requirements inventory keyed by
   `FR-###`/`SC-###` (SCs limited to ones requiring *buildable* work — excludes business-KPI
   style outcomes like "reduce support tickets by 50%"), a task-coverage map, a constitution
   rule set.
4. **Six detection passes**: (A) Duplication — near-duplicate requirements; (B) Ambiguity —
   vague adjectives ("fast", "scalable", "secure", "intuitive") and unresolved placeholders
   (`TODO`, `???`); (C) Underspecification — verbs with no measurable object, tasks referencing
   undefined files/components; (D) Constitution Alignment — anything conflicting with a MUST;
   (E) Coverage Gaps — requirements with zero tasks, tasks with no requirement, buildable SCs
   with no task; (F) Inconsistency — terminology drift, entities in plan but not spec (or
   vice versa), ordering contradictions, directly conflicting requirements (e.g. "Next.js" vs
   "Vue" in different files).
5. **Severity**: CRITICAL (constitution MUST violation, missing core artifact, zero-coverage
   requirement blocking baseline function) > HIGH (duplicate/conflicting requirement, ambiguous
   security/perf attribute, untestable acceptance criterion) > MEDIUM (terminology drift,
   missing non-functional coverage) > LOW (wording only).
6. Output a single markdown table (max 50 rows, overflow summarized), a requirement-coverage
   table, a constitution-alignment list, an unmapped-tasks list, and summary metrics (total
   requirements/tasks, coverage %, ambiguity/duplication/critical counts).
7. End with explicit **Next Actions** (which command to re-run for which fix) and an offer:
   "Would you like me to suggest concrete remediation edits for the top N issues?" — never
   applied automatically.

## 7. `speckit-implement`

**One-liner**: Execute the implementation plan by processing and executing all tasks defined in
`tasks.md`.

**When**: Once tasks.md (and ideally checklists) are ready. This is the only skill that writes
application source code.

**Algorithm**:
1. `check-prerequisites.sh --json --require-tasks --include-tasks`.
2. **Checklist gate**: if `checklists/` exists, tally total/completed/incomplete per file into
   a status table. If any checklist has incomplete items: show the table, **stop**, and ask the
   user explicitly "proceed anyway? (yes/no)" — only continue on an affirmative answer. If all
   pass: show the table and auto-continue.
3. Load `tasks.md` (required), `plan.md` (required), and whatever of
   `data-model.md`/`contracts/`/`research.md`/`constitution.md`/`quickstart.md` exist.
4. **Project setup verification**: detect the actual tech present (git repo? Dockerfile?
   `.eslintrc*`? `.prettierrc*`? `*.tf`? helm charts?) and create/verify the corresponding
   ignore file (`.gitignore`, `.dockerignore`, `.eslintignore`, `.prettierignore`, `.npmignore`,
   `.terraformignore`, `.helmignore`) with the right patterns for *that* technology — append
   missing critical patterns to an existing file rather than overwriting it.
5. Parse phases/dependencies/`[P]` markers/file-paths out of `tasks.md`, then execute
   **phase-by-phase**: complete a phase fully before starting the next; within a phase, run
   `[P]` tasks together and sequential tasks in order; if tests were requested, they run
   *before* their corresponding implementation task (TDD); tasks touching the same file must
   not run concurrently.
6. Progress/error handling: report after each completed task; **halt** on a non-parallel task
   failure; for a failed `[P]` task, keep going with the others and report the failure
   separately; **mark completed tasks `[X]` in `tasks.md`** as they finish (this is how
   `speckit-converge` and a re-run of `speckit-implement` know what's left).
7. Final validation: all required tasks done, implementation matches spec, tests (if any)
   pass, plan's technical decisions were actually followed.

## 8. `speckit-converge`

**One-liner**: Assess the current codebase against the feature's spec, plan, and tasks, then
append any remaining unbuilt work as new tasks to `tasks.md` so implement can complete it.

**When**: After at least one `speckit-implement` pass, to catch drift. **This is not a git
diff tool** — it inspects the *current state* of the code against the *current* artifacts, with
no history/branch awareness.

**Operating constraint — append-only, never rewrite**: the *only* write this skill is allowed
to make is appending one new `## Phase N: Convergence` section to the bottom of `tasks.md`. It
must never touch `spec.md`/`plan.md`, never renumber/reorder/delete any existing task (including
tasks from a *prior* Convergence phase), and never touch application code directly — fixing the
gap is `speckit-implement`'s job on a follow-up run. If nothing is missing, `tasks.md` must come
out **byte-for-byte unchanged** (no empty header either).

**Algorithm**:
1. `check-prerequisites.sh --json --require-tasks --include-tasks`; hard-stop naming the
   missing prerequisite command if spec/plan/tasks aren't all present — no partial output.
2. Load spec (FRs, buildable SCs, user-story acceptance scenarios, edge cases), plan
   (architecture/data-model refs/phases/named touch-points), tasks (IDs — to compute next ID
   and next phase number), constitution (unless it's still an unfilled template, in which case
   constitution checks are skipped gracefully rather than failing).
3. Build an **intent inventory** (one stable key per FR/SC/acceptance-scenario, plus plan
   decisions and constitution MUSTs) and a **code-scope map** (the files/components the
   artifacts actually name, plus a keyword search for the concepts they describe) — the
   assessment is bounded to this scope; it must not infer scope beyond what the artifacts say.
4. For each inventory item, classify any gap as exactly one of: `missing` (absent entirely),
   `partial` (exists but doesn't fully satisfy it), `contradicts` (code actively conflicts with
   stated intent or a constitution MUST), `unrequested` (code does something nobody asked for —
   surfaced for awareness, never auto-removed). Little-to-no code yet → treat the whole scope
   as `missing` rather than failing; nothing missing at all → zero findings.
5. Severity: CRITICAL (constitution MUST violation, or missing/contradicts blocking a P1
   story) > HIGH (missing/partial on a core FR/acceptance-criterion) > MEDIUM (partial on a
   secondary requirement, or unjustified unrequested addition) > LOW (polish-level).
6. **Present the findings table in-chat first**, before writing anything — ID / gap-type /
   severity / source-ref / evidence / remaining-work — plus summary metrics.
7. **If actionable findings exist**: append `## Phase N: Convergence` (N = highest existing
   phase + 1) to `tasks.md`, one `- [ ] T{next} <description> per <source-ref> (<gap-type>)`
   line per finding, CRITICAL/HIGH ordered first, IDs continuing from the current max — never
   reusing or renumbering. **If none exist**: don't touch the file at all; report
   `✅ Converged — the implementation satisfies the spec, plan, and tasks.`
8. Recommend next step: `tasks_appended` → run `/speckit-implement` again (a follow-up converge
   run should find strictly fewer items); `converged` → proceed to review/PR, no further
   implement pass needed for this feature's specified scope.

## 9. `speckit-taskstoissues`

**One-liner**: Convert existing tasks into actionable, dependency-ordered GitHub issues for the
feature based on available design artifacts.

**When**: Optional, only meaningful when the repo's git remote is actually GitHub.

**Algorithm**:
1. `check-prerequisites.sh --json --require-tasks --include-tasks` → path to `tasks.md`.
2. `git config --get remote.origin.url` — **hard gate**: proceed only if this resolves to a
   GitHub URL; the skill explicitly warns it must under no circumstances create issues in a
   repository that doesn't match the remote.
3. **Deduplication before creating anything**: collect every task ID (`T\d{3}` pattern) from
   `tasks.md`, then page through the repo's existing issues via the GitHub MCP server's
   `list_issues` (no `state` filter, so both open+closed are checked; `perPage: 100`,
   cursor-paginated via `after`/`endCursor`), matching each issue title against `\bT\d{3}\b`
   (word-boundaried so `ST001`/`T0010` aren't false-matched) to mark which task IDs already
   have an issue. Stop paginating as soon as every task ID is accounted for, or pages run out —
   this bounds API calls even on repos with large issue histories.
4. For each task **not** already covered: strip the leading `- [ ]` and any `[P]`/`[US#]`
   markers to recover `<ID>` and `<description>`, create a GitHub issue titled
   `T001: <description>` (ID once, followed by the description). Skip (and report) any task
   whose ID was already matched in step 3.

**This is the only skill that touches an external, shared system (GitHub Issues)** — treat it
with the same care as any other action with real, hard-to-reverse, visible-to-others blast
radius.

## Shared envelope (every skill, not repeated above)

- **User Input**: the raw `$ARGUMENTS` text the user typed after the slash command — always
  considered if non-empty.
- **Pre-Execution Checks**: look for `.specify/extensions.yml`; for the relevant `before_*`
  hook key, skip disabled hooks, don't evaluate `condition` expressions (leave that to the
  external `HookExecutor`), render mandatory hooks as an `EXECUTE_COMMAND:` block that must
  actually be invoked and awaited, render optional hooks as a suggestion only. Silently skip
  all of this if the file doesn't exist or has no relevant entries.
- **Mandatory/Post-Execution Hooks**: identical mechanism for the `after_*` key, run right
  before the skill's own Completion Report.
- **Single-quote escaping note**: every skill that shells out reminds itself to escape
  arguments like `I'm Groot` as `'I'\''m Groot'` (or prefer double quotes) when building shell
  command strings.
