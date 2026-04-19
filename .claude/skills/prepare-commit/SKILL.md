---
name: prepare-commit
description: Use this skill when the user asks to prepare a commit, write a commit message, stage files, create a branch, or prepare PR notes for Signalist.
---

# Prepare Commit

Use this skill to prepare a commit and PR context without silently performing protected git actions.

Read first:
- `AGENTS.md`
- `Makefile`
- `frontend/package.json`

Read as needed:
- `git status`
- `git diff`
- `git log`

Workflow:
1. Inspect the current branch and working tree.
2. Stage the intended files with `git add`.
3. Run the repository quality checks relevant to the current change.
4. Review the change for correctness, architecture, validation, regressions, and test coverage.
5. Review the change with a security lens.
6. If the current branch is `main`, `master`, `develop`, or another protected/shared branch, create a dedicated working branch first.
7. Build a Conventional Commit title and optional body from the actual change.
8. Prepare PR-ready notes:
   - what
   - why
   - how
   - tests or verification
   - risks or follow-up
9. Prepare a checkbox-style verification list using the repo's actual quality gates.
10. If the user explicitly asks to commit, ask for confirmation before running `git commit`.
11. If the user explicitly asks to push, ask for confirmation before running `git push`.

Rules:
- Never commit or push without explicit confirmation in the current conversation.
- Do not assume all changed files belong to the intended commit.
- Keep the commit message human-readable and reviewer-friendly.
- Prefer the repository's real verification commands over generic checklist items.
- If pre-commit checks find blockers, report them before proposing the final commit.
