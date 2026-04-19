---
name: verify-quality
description: Use this skill when the user asks to run checks, validate a change, see whether code is ready, or investigate lint, analysis, test, or security-gate results in Signalist.
---

# Verify Quality

Use this skill to run the repository's real quality gates in a predictable order.

Read first:
- `AGENTS.md`
- `.claude/rules/testing.md`

Read as needed:
- `Makefile`
- `composer.json`
- `frontend/package.json`

Workflow:
1. Discover the canonical commands exposed by the repository.
2. Prefer project wrappers such as `make` targets over raw vendor binaries.
3. Run the narrowest relevant tests first, then broader required checks.
4. Report failures with the command, affected area, and smallest likely fix direction.

Typical order:
1. `make quality`
2. `make tests-unit`
3. `make tests-integration`
4. `make tests`
5. `make tests-api`
6. `make front-lint`
7. `make front-test`
8. `npm run typecheck` from `frontend/` when frontend TypeScript changed
9. `make grumphp` when the broader pre-commit gate is requested

Rules:
- Do not invent substitute commands silently.
- If endpoint behavior changed, do not stop at unit tests only.
- If frontend behavior changed, do not stop at backend checks only.
