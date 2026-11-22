# The Spec-Kit + Claude Skills Stack

> Canonical (English) source of truth. See `README.uk.md` for a Ukrainian translation.

This project uses **[GitHub Spec-Kit](https://github.com/github/spec-kit)** ("speckit") as its
Spec-Driven Development (SDD) toolkit, wired into Claude Code as a set of **Skills**
(`.claude/skills/speckit-*/SKILL.md`) rather than slash-command markdown files. This is the
only non-generic, project-installed tooling found in this repository — everything else
available to the agent (Postman, dataviz, review, etc.) is a global/plugin skill, not
something this project's own `.claude/` and `.specify/` directories declare.

This folder documents that stack in enough depth that another project could stand up an
identical pipeline. Read in this order:

1. **README.md** (this file) — map of the pieces and the end-to-end lifecycle.
2. **ARCHITECTURE.md** — the `.specify/` runtime: config files, scripts, template
   resolution/override stack, the extension-hooks mechanism, the declarative workflow file.
3. **SKILLS.md** — a deep-dive on each of the 10 skills: trigger, inputs, outputs, exact
   algorithm, limits/edge-cases, output format.
4. **BOOTSTRAP.md** — step-by-step instructions to install this exact stack in a brand-new
   project.

## What problem this solves

Spec-Kit turns "write a feature" into a repeatable pipeline with checkpoints, so that an LLM
agent (or a human) never jumps straight from a one-line request to code. Every feature gets
its own directory under `specs/` containing a specification, a plan, and a task list, all
derived from each other and from a project *constitution* (non-negotiable principles). The
pipeline is designed so each stage's output is the next stage's required input — you cannot
skip steps without explicit overrides.

## The 10 skills, in lifecycle order

| # | Skill | Runs when | Reads | Writes |
|---|-------|-----------|-------|--------|
| 0 | `speckit-constitution` | Project setup, or whenever governing principles change | `.specify/memory/constitution.md`, repo context | `.specify/memory/constitution.md` (+ propagates to templates/commands) |
| 1 | `speckit-specify` | Starting a new feature | User's free-text feature description, constitution | New `specs/NNN-short-name/spec.md`, `.specify/feature.json`, `checklists/requirements.md` |
| 2 | `speckit-clarify` | After specify, before plan | `spec.md`, constitution | `spec.md` (adds `## Clarifications` section), re-checks `checklists/requirements.md` |
| 3 | `speckit-plan` | After spec is clear | `spec.md`, constitution, `plan-template.md` | `plan.md`, `research.md`, `data-model.md`, `contracts/*`, `quickstart.md` |
| 4 | `speckit-tasks` | After plan | `plan.md`, `spec.md`, `data-model.md`, `contracts/`, `research.md` | `tasks.md` (dependency-ordered, per-user-story checklist) |
| 5 | `speckit-checklist` | Any time after spec exists | `spec.md`/`plan.md`/`tasks.md`, constitution | `checklists/<domain>.md` ("unit tests for English") |
| 6 | `speckit-analyze` | After tasks, before implement | `spec.md`, `plan.md`, `tasks.md`, constitution | Nothing — read-only report in chat |
| 7 | `speckit-implement` | Once tasks + checklists are ready | `tasks.md`, `plan.md`, `data-model.md`, `contracts/`, `constitution` | Application source code; marks tasks `[X]` in `tasks.md` |
| 8 | `speckit-converge` | After an implement pass, to catch drift | `spec.md`, `plan.md`, `tasks.md`, current codebase | Appends a `## Phase N: Convergence` section to `tasks.md` (append-only) |
| 9 | `speckit-taskstoissues` | Optional, GitHub remote only | `tasks.md`, `git remote` | GitHub Issues (one per task, deduped by `T###` in the title) |

`speckit-analyze` and `speckit-checklist` are **quality gates**, not pipeline stages — they
can be invoked repeatedly and never mutate `spec.md`/`plan.md`/`tasks.md` structure beyond
checkbox toggling. `speckit-converge` is the only command meant to run *after*
`speckit-implement`, to reconcile drift between what was planned and what actually got built.

## End-to-end lifecycle

```
/speckit-constitution   (once per project, revisit when principles change)
        │
        ▼
/speckit-specify "<feature description>"
        │  → specs/NNN-short-name/spec.md
        │  → specs/NNN-short-name/checklists/requirements.md (auto quality gate)
        ▼
/speckit-clarify                              (≤5 targeted Q&A, encoded into spec.md)
        ▼
/speckit-plan                                  → plan.md, research.md, data-model.md,
        │                                         contracts/, quickstart.md
        ▼
/speckit-tasks                                  → tasks.md (phased, per-user-story, [P]-marked)
        │
        ├─ /speckit-checklist <domain>          (optional, repeatable quality gate)
        ├─ /speckit-analyze                     (optional, read-only cross-artifact report)
        │
        ▼
/speckit-implement                              → actual code, tasks.md checkboxes flipped
        │
        ▼
/speckit-converge                               (optional) → appends remaining-work tasks
        │
        ▼
/speckit-taskstoissues                          (optional, GitHub only) → one Issue per task
```

Each skill's own `SKILL.md` enforces this order defensively: `speckit-clarify` warns if run
without a spec, `speckit-plan`/`speckit-tasks`/`speckit-implement` hard-fail via
`check-prerequisites.sh` if their required upstream artifact is missing, and
`speckit-analyze`/`speckit-converge` refuse to run until `tasks.md` exists.

## Directory map

```
.claude/skills/speckit-*/SKILL.md     # the 10 skill definitions (this is what Claude Code loads)
.specify/
├── feature.json                      # pointer to the *currently active* feature dir
├── init-options.json                 # how this project was initialized (ai, numbering style…)
├── integration.json                  # which AI integration is installed + its invoke style
├── memory/
│   └── constitution.md               # THIS project's non-negotiable principles
├── scripts/bash/                     # shared shell helpers every skill shells out to
│   ├── common.sh
│   ├── create-new-feature.sh
│   ├── check-prerequisites.sh
│   ├── setup-plan.sh
│   └── setup-tasks.sh
├── templates/                        # canonical skeletons copied into each feature dir
│   ├── spec-template.md
│   ├── plan-template.md
│   ├── tasks-template.md
│   └── checklist-template.md
└── workflows/speckit/workflow.yml    # declarative macro-workflow (specify→plan→tasks→implement)

specs/
└── NNN-short-feature-name/           # one directory per feature, sequential 3-digit prefix
    ├── spec.md
    ├── checklists/
    │   ├── requirements.md           # auto-generated by speckit-specify
    │   └── <domain>.md               # manually requested via speckit-checklist
    ├── plan.md
    ├── research.md
    ├── data-model.md
    ├── contracts/
    ├── quickstart.md
    └── tasks.md
```

## What is unique to *this* project

The stack itself (scripts, templates, skill definitions) is the stock, unmodified Spec-Kit
distribution — nothing here was hand-written for this repo. The one project-specific artifact
is `.specify/memory/constitution.md`, which encodes two principles tailored to this codebase:

1. **Simplicity & YAGNI** — no speculative abstractions; prefer Laravel's built-ins.
2. **Laravel & Framework Conventions** — thin controllers, Form Requests, business logic in
   models/actions/services, use the installed packages (Jetstream, Sanctum,
   spatie/laravel-permission, spatie/laravel-medialibrary, owen-it/laravel-auditing) instead of
   reinventing them, Pint-clean code style.

Every `/speckit-plan` run gates against these two principles. A new project adopting this stack
should **not** copy this constitution verbatim — run `/speckit-constitution` fresh and derive
principles from that project's actual stack and team size (see BOOTSTRAP.md).

This repo has also adopted one **local convention that is not part of stock Spec-Kit**: every
artifact produced inside `specs/<feature>/` gets a sibling `*.uk.md` Ukrainian translation
(e.g. `spec.md` → `spec.uk.md`), kept in sync as a read-only convenience copy. The English file
remains canonical because Spec-Kit's own tooling (scripts, skills) only ever reads the
un-suffixed filename.

It also has one **local orchestrator skill**, `/tt-feature` (`.claude/skills/tt-feature/SKILL.md`),
which runs the lifecycle above for one task in a single command: codebase and risk review →
specify → clarify → plan → tasks → analyze → implement (tests, Pint, `code-reviewer`). It takes a
task description or a path to a `.md` file. By default it asks questions through `AskUserQuestion`
(in Ukrainian) and stops for confirmation before implementing; with «без підтверджень» in the
arguments it runs end to end, decides open questions itself and lists those decisions in its final
report. It never commits, pushes or deploys on its own. Called without a description, it resumes
the active feature from `.specify/feature.json`.

## Real example in this repo

`specs/008-purchase-request-api/` shows a feature that has been through
specify → plan → tasks (contains `spec.md`, `plan.md`, `tasks.md`, `research.md`,
`data-model.md`, `quickstart.md`, `contracts/`, `checklists/`). Seven earlier features
(`001`…`007`) show the same pattern at various stages of completion — a good reference set for
what "real" artifacts produced by this pipeline look like, as opposed to the templates.
