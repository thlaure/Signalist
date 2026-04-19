Perform a structured code review for a Signalist change.

Review scope: `$ARGUMENTS`

If the scope is omitted, inspect the current git diff.

Review checklist:
1. Confirm the change respects the repository architecture and `AGENTS.md`.
2. Check that API Platform resources, state processors, controllers, hooks, and components remain orchestration-focused.
3. Check that business rules stay in handlers, domain services, or message handlers.
4. Check validation at the backend input boundary and safe handling in the frontend.
5. Check persistence, AI, RSS, and external integration code for leaked business logic or unsafe coupling.
6. Check error handling and user-visible behavior consistency.
7. Check test completeness for backend, API, and frontend behavior.
8. Check that the change can pass the repo quality gates.

Prioritize findings:
- correctness bugs
- security regressions
- architectural regressions
- missing validation or tests
- maintainability issues

Response format:
- findings first, ordered by severity
- then open questions or assumptions
- then a short summary only if useful

Do not focus on style nits already covered by formatters, ESLint, or static analysis unless they expose real risk.
