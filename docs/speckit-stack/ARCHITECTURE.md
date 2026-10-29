# `.specify/` Runtime Architecture

> Canonical (English) source of truth. See `ARCHITECTURE.uk.md` for a Ukrainian translation.

Every `speckit-*` skill is a markdown prompt (`SKILL.md`) that the agent follows, but the
*state* and *mechanics* of the pipeline live in `.specify/`. This document explains each piece.

## Config files

### `.specify/feature.json`

```json
{ "feature_directory": "specs/008-purchase-request-api" }
```

The single pointer to "which feature are we working on right now." Written by
`create-new-feature.sh` (invoked indirectly by `speckit-specify`) and read by every other
skill's helper scripts via `get_feature_paths()` in `common.sh`. Resolution order when a skill
needs the active feature directory:

1. `SPECIFY_FEATURE_DIRECTORY` environment variable, if the user/session set one explicitly
   (and if so, it's persisted back into `feature.json` so later commands in later sessions
   still pick it up).
2. `.specify/feature.json`'s `feature_directory` key.
3. Otherwise: hard error telling the caller to run `speckit-specify` first.

Read-only callers (e.g. `check-prerequisites.sh --paths-only`) pass `--no-persist` so merely
*asking* what the active feature is never has the side effect of writing to the working tree.

### `.specify/init-options.json`

```json
{
  "ai": "claude",
  "ai_skills": true,
  "feature_numbering": "sequential",
  "here": true,
  "integration": "claude",
  "script": "sh",
  "speckit_version": "0.14.2.dev0"
}
```

Frozen at `specify init` time. Key fields:

- **`ai` / `integration`**: which coding agent this project was wired for (`claude`, `copilot`,
  `gemini`, `opencode`, …). Determines whether commands live as `.claude/skills/speckit-*/`,
  `.github/agents/`, etc.
- **`ai_skills`**: `true` means the integration uses the *Skills* mechanism (one `SKILL.md`
  per command, auto-discovered) rather than flat slash-command markdown files.
- **`feature_numbering`**: `"sequential"` → `specs/001-foo`, `002-bar`, … (3-digit, zero-padded,
  computed by scanning existing `specs/` directories). The alternative is `"timestamp"` →
  `specs/20260319-143022-foo`. (A deprecated `branch_numbering` key is still read as a fallback
  for older installs, with a one-line deprecation warning emitted if seen.)
- **`script`**: `"sh"` means Spec-Kit generated POSIX/bash helper scripts (`.specify/scripts/bash/`)
  rather than PowerShell equivalents.
- **`speckit_version`**: pinned version this project was initialized/upgraded with.

### `.specify/integration.json`

```json
{
  "integration_settings": {
    "claude": { "script": "sh", "invoke_separator": "-" }
  },
  "default_integration": "claude"
}
```

Tells the helper scripts how to *render* a command name back to the user. `invoke_separator`
is `"-"` for Claude Skills (so `speckit.git.commit` → `/speckit-git-commit`), but `"."` for
integrations that keep the dotted form. `format_speckit_command()` in `common.sh` uses this to
print correct hook-invocation hints (e.g. in the extension-hooks blocks every skill emits).

### `.specify/memory/constitution.md`

The project's governing document — see README.md's "What is unique to this project" section
for *this* repo's actual two principles. Structurally: an HTML-comment **Sync Impact Report**
at the top (version diff, which templates were checked for consistency, deferred TODOs),
then `## Core Principles` (one `###` subsection per principle, each with a rationale), then
`## Governance` (amendment procedure, versioning rule, compliance-review expectation).
Versioned with semver: MAJOR = principle removed/redefined incompatibly, MINOR = principle
added, PATCH = wording only. Only `speckit-constitution` is allowed to write this file.

## Shared shell helpers (`.specify/scripts/bash/`)

All skills shell out to these instead of duplicating path/JSON logic inline.

### `common.sh`

The shared library, sourced by every other script. Notable functions:

- **`get_repo_root()`** — walks upward from `pwd` looking for a `.specify/` directory (the
  project marker), so Spec-Kit always resolves to *this* project's root even if invoked from a
  nested package in a monorepo. Honors a `SPECIFY_INIT_DIR` override for CI/non-interactive use.
- **`get_feature_paths()`** — the single source of truth for every path a skill needs:
  `REPO_ROOT`, `CURRENT_BRANCH`, `FEATURE_DIR`, `FEATURE_SPEC`, `IMPL_PLAN`, `TASKS`,
  `RESEARCH`, `DATA_MODEL`, `QUICKSTART`, `CONTRACTS_DIR` — all emitted as `KEY=value` lines
  meant to be `eval`'d by the caller, with values safely shell-quoted via `printf '%q'`.
- **`resolve_template(name, repo_root)`** — the **template override stack**: looks for a given
  template name in, in priority order, (1) `.specify/templates/overrides/`, (2) installed
  presets under `.specify/presets/<id>/templates/` (sorted by a `priority` field in a
  `.registry` manifest), (3) `.specify/extensions/<id>/templates/`, (4) the core
  `.specify/templates/`. This is what lets a project or an installed preset/extension override
  the default `spec-template.md` without touching the core file.
- **`resolve_template_content(name, repo_root)`** — a richer variant that supports
  **composition strategies** beyond plain override-wins: a layer can declare `replace`,
  `prepend`, `append`, or `wrap` (with a `{CORE_TEMPLATE}` placeholder) in its
  `preset.yml` manifest, letting multiple layers *combine* into one template instead of one
  fully replacing another.
- **`get_invoke_separator()` / `format_speckit_command()`** — read `integration.json` to render
  the correct slash-command spelling for the active integration (see above).
- **`json_escape()`** — a pure-bash JSON string escaper (RFC 8259 control-character handling)
  used as the fallback whenever `jq` isn't installed.

### `create-new-feature.sh`

Invoked when a new feature is specified. Responsibilities:

- Generates a short branch-safe suffix from the free-text description: lowercases, strips stop
  words (articles, pronouns, common verbs), keeps meaningful words ≥3 chars (or shorter words
  if they appear as an uppercase acronym in the original text, e.g. "API"), joins the first 3–4
  into a hyphenated slug.
- Numbers it: scans `specs/` for existing `NNN-*` directories (ignoring timestamp-style dirs),
  takes the highest found number, increments. Supports an explicit `--number N` override that
  re-resolves to the next free number if `N` is already taken (never silently overwrites an
  existing spec directory).
- Enforces GitHub's 244-byte branch-name limit, truncating the suffix (not the numeric prefix)
  if needed, with a warning on stderr.
- Creates `specs/NNN-suffix/`, copies the resolved `spec-template.md` into `spec.md` if absent,
  and persists the new feature directory into `.specify/feature.json`.
- `--dry-run` computes names/paths without touching disk; `--allow-existing-branch` lets the
  caller reuse a directory that already exists instead of erroring.

### `check-prerequisites.sh`

The gatekeeper every later-stage skill calls before doing anything. Flags:

- `--paths-only` — pure path resolution, zero validation, used by `speckit-clarify` early on.
- (no flag) — requires `FEATURE_DIR` and `plan.md` to exist; errors out naming the skill to run
  instead (`speckit-specify` / `speckit-plan`) if not.
- `--require-tasks` — additionally requires `tasks.md` to exist (used by `speckit-implement`,
  `speckit-analyze`, `speckit-converge`, `speckit-taskstoissues`).
- `--include-tasks` — includes `tasks.md` in the `AVAILABLE_DOCS` list it reports (separate
  from *requiring* it to exist).

Emits either a JSON object (`--json`) or a human-readable text block listing which optional
docs (`research.md`, `data-model.md`, `contracts/`, `quickstart.md`, `tasks.md`) were found.

### `setup-plan.sh` / `setup-tasks.sh`

Thin wrappers used by `speckit-plan` / `speckit-tasks` respectively: resolve feature paths,
copy the relevant template into place if the target file doesn't exist yet (idempotent — a
second run won't clobber an existing `plan.md`), and emit the JSON payload the skill expects
(`FEATURE_SPEC`, `IMPL_PLAN`, `SPECS_DIR`, `BRANCH` for plan; `FEATURE_DIR`, `AVAILABLE_DOCS`,
`TASKS_TEMPLATE` for tasks).

## Templates (`.specify/templates/`)

Four canonical skeletons, each with a strict section structure the corresponding skill fills
in (placeholders in `[ALL_CAPS]` or `[bracketed]` form):

- **`spec-template.md`** — `User Scenarios & Testing` (prioritized, independently-testable
  user stories with Given/When/Then acceptance scenarios), `Edge Cases`, `Requirements`
  (`FR-###` functional requirements, `Key Entities`), `Success Criteria` (`SC-###`, must be
  measurable and technology-agnostic), `Assumptions`.
- **`plan-template.md`** — `Summary`, `Technical Context` (language, dependencies, storage,
  testing, platform, performance/scale), `Constitution Check` (gate), `Project Structure`
  (with alternative layout options for single-project / web-app / mobile+API, the unused ones
  deleted by the skill), `Complexity Tracking` (filled only if a constitution gate is violated
  and the violation is justified rather than avoided).
- **`tasks-template.md`** — phase-structured: `Phase 1: Setup` → `Phase 2: Foundational
  (blocking)` → `Phase 3+: one phase per user story, in priority order` → `Final Phase: Polish`.
  Every task line is `- [ ] T### [P?] [USx?] Description with exact file path`.
- **`checklist-template.md`** — referenced by `speckit-checklist` for title/meta/category
  formatting; falls back to a simple `## Category` + `- [ ] CHK### item` convention if absent.

Projects/presets/extensions can **override** any of these (see `resolve_template` above)
without touching the core copies, so an organization-wide preset can, e.g., add a mandatory
"Observability" section to every plan without forking Spec-Kit itself.

## The extension-hooks mechanism (`.specify/extensions.yml`)

**Not present in this repo**, but every single skill defends against it, so it's worth
documenting: if `.specify/extensions.yml` exists, each skill checks for entries under a
command-specific key (`hooks.before_specify`, `hooks.after_plan`, etc.). Each hook entry can be:

- `enabled: false` → skipped (default is enabled if the field is absent).
- Have a non-empty `condition` → the skill does **not** attempt to evaluate it itself; that's
  left to a `HookExecutor` implementation outside the skill's own logic.
- `optional: true` → the skill prints the hook as a suggestion (`Command: /{command}` +
  `Prompt:` text) and waits for the user to invoke it manually.
- `optional: false` (mandatory) → the skill must actually invoke it (`EXECUTE_COMMAND:` block)
  and block on its result before continuing.

Hook command names are dotted (`speckit.git.commit`) in the YAML but rendered with the active
integration's separator (`/speckit-git-commit` for this Claude-Skills project). The canonical
real-world use of this mechanism is a **git extension** that creates/switches a feature branch
as a `before_specify` hook — this project doesn't have one installed, so `speckit-specify`
creates the `specs/NNN-foo` directory but does **not** touch git branches.

## The declarative workflow (`.specify/workflows/speckit/workflow.yml`)

A higher-level, non-agent-executed description of the "full cycle" (specify → plan → tasks →
implement) with two human **review gates** (`type: gate`, `on_reject: abort`) inserted after
spec and after plan. This file is metadata for Spec-Kit-aware orchestration tooling (e.g. a
CI/automation runner that drives the whole pipeline non-interactively) — the Claude Skills
themselves don't read it; a human invoking `/speckit-specify`, `/speckit-clarify`, etc. by hand
*is* effectively walking this same graph with the review gates being "does the human like the
output enough to move on."

## Feature directory layout

```
specs/NNN-short-name/
├── spec.md                 # speckit-specify (+ speckit-clarify appends)
├── checklists/
│   ├── requirements.md     # auto-created by speckit-specify, re-validated by speckit-clarify
│   └── <domain>.md         # speckit-checklist, one file per invoked domain, append-only
├── plan.md                 # speckit-plan
├── research.md             # speckit-plan, Phase 0
├── data-model.md           # speckit-plan, Phase 1
├── contracts/               # speckit-plan, Phase 1 (skipped for purely-internal features)
├── quickstart.md           # speckit-plan, Phase 1 (validation/run guide, no implementation code)
└── tasks.md                # speckit-tasks (+ speckit-implement checks off [X], speckit-converge appends)
```
