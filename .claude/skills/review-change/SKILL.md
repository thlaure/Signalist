---
name: review-change
description: Use this skill when the user asks for a code review, a correctness pass, a check before PR, or asks whether a Signalist change follows project conventions.
---

# Review Change

Use this skill to review a change for correctness, architecture, and test completeness.

Read first:
- `AGENTS.md`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`
- `.claude/rules/security.md`

Read as needed:
- `.claude/patterns.md`
- the relevant diff and nearby files

Workflow:
1. Inspect the diff or the requested scope.
2. Check architecture and layer ownership across backend and frontend.
3. Check validation, persistence boundaries, async boundaries, and error handling consistency.
4. Check test coverage for the changed behavior.
5. Check whether the change is likely to pass the repository quality gates.

Response format:
- findings first, ordered by severity
- then assumptions or open questions
- then a short summary only if useful

Rules:
- Focus on correctness, security, regressions, and missing tests.
- Do not spend time on style nits already covered by tooling unless they expose real risk.
