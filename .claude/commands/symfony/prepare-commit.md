Prepare a commit for the current changes using Conventional Commits and repo-aware verification notes.

Scope or context: `$ARGUMENTS`

Workflow:
1. Inspect `git status`, the current branch, and the current diff.
2. Stage the intended files with `git add`.
3. Run the repository quality checks relevant to the current change, using the real project gates from `Makefile` and `frontend/package.json`.
4. Review the change for correctness, architecture, validation, regressions, and test completeness.
5. Review the change with a security lens: auth/authz, input validation, secrets, external calls, privacy, and data exposure.
6. If the current branch is `main`, `master`, `develop`, or another protected/shared branch, create a dedicated branch before committing.
7. Build a Conventional Commit message that matches the actual change scope.
8. Prepare PR-ready notes that explain:
   - what changed
   - why it changed
   - how it was implemented
   - what was verified
   - remaining risks or follow-up points
9. Prepare a verification checklist based on the real project gates from `Makefile` and the frontend scripts.
10. If the user explicitly asks to commit, ask for confirmation before running `git commit`.
11. If the user explicitly asks to push, ask for confirmation before running `git push`.

Rules:
- Never commit or push silently.
- Treat `git commit` and `git push` as confirmation-required actions even when the user previously asked for preparation work.
- Stage only the files that belong to the intended change.
- Prefer a short, accurate Conventional Commit title over a clever one.
- Do not invent verification steps that do not exist in the repository.
- If quality, review, or security checks reveal blockers, surface them before proposing the final commit.

Output format:
- `Pre-commit checks`
- `Suggested branch`
- `Suggested commit title`
- `Suggested commit body`
- `PR notes`
- `Verification checklist`
