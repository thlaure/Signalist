Prepare and implement an urgent Signalist hotfix with the smallest safe change set.

Production issue or urgent scope: `$ARGUMENTS`

Workflow:
1. Confirm the issue is truly urgent and production-facing.
2. Reproduce or localize the failure quickly from logs, alerts, or the current diff.
3. Identify the narrowest safe fix with the smallest blast radius.
4. Prefer a dedicated `hotfix/...` branch if the current branch is shared or protected.
5. Implement the minimal change only. No opportunistic refactor.
6. Add the smallest regression coverage the repo can express.
7. Run the narrowest relevant verification, then the broader checks required by the touched area.
8. Prepare a Conventional Commit message and deployment notes.
9. Ask for confirmation before any `git commit` or `git push`.

Rules:
- Speed matters, but correctness still beats panic.
- If the real fix is large, prefer a safe containment change plus a follow-up task.
- Keep the diff extremely narrow and easy to review.
- Surface any skipped verification explicitly.
