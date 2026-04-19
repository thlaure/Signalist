---
name: bug-fix
description: Use this skill when the user reports broken behavior, errors, regressions, failing tests, or asks to fix a bug in Signalist.
---

# Bug Fix

Use this skill for a small, repeatable regression-first bug-fix workflow.

Read first:
- `AGENTS.md`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`
- `.claude/rules/security.md`

Read as needed:
- `.claude/patterns.md`
- nearby files in the failing path

Workflow:
1. Reproduce the issue from the report, logs, failing test, or current behavior.
2. Isolate the narrowest code path that explains the failure.
3. Identify the root cause before editing code.
4. Implement the smallest clean fix.
5. Add or update a regression test when the bug can be reproduced automatically.
6. Run the relevant quality gates before reporting completion.

Rules:
- Reuse local patterns instead of introducing a new structure.
- Prefer the smallest understandable fix over broad refactoring.
- If API Platform or the existing frontend stack already provides the correct behavior directly, use it.
- Choose readability over performance unless there is a measured bottleneck.
