---
name: scan-project
description: Use this skill when the user asks to explore, inspect, understand, map, or analyze the Signalist repository before making changes.
---

# Scan Project

Use this skill to build an implementation-ready map of the repository before coding.

Read first:
- `AGENTS.md`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`

Read as needed:
- `README.md`
- `composer.json`
- `Makefile`
- `frontend/package.json`
- `.claude/patterns.md`

Workflow:
1. Inspect the project shape: `src/`, `frontend/`, `config/`, `tests/`.
2. Identify the active conventions: Symfony/API Platform usage, React/Vite usage, Messenger flows, quality gates, and testing stack.
3. Find the nearest local example for the area the user wants to change.
4. Summarize architecture, likely files to touch, and main risks.

Rules:
- Prefer local repository patterns over framework defaults.
- Do not invent new layers or folders during the exploration phase.
- Surface any `AGENTS.md` rule that constrains implementation decisions.
