Fix a Signalist bug using a small, repeatable regression-first workflow.

Bug report or scope: `$ARGUMENTS`

Execution order:
1. Reproduce the bug from the user report, failing test, logs, or current behavior.
2. Find the narrowest code path that explains the failure.
3. Identify the root cause before changing code.
4. Implement the smallest fix that resolves the bug cleanly.
5. Add or update a regression test in the same session.
6. Run the relevant repository quality gates before reporting completion.

Default expectations unless the repo clearly differs:
- Prefer the smallest understandable fix over broad refactoring.
- Keep the fix easy for a human reviewer to understand.
- If API Platform, React Query, or another existing project feature already provides the correct behavior directly, use it instead of adding extra layers.
- If performance and readability conflict and there is no measured bottleneck, choose readability.

Checklist:
1. State expected behavior versus actual behavior.
2. Point to the concrete failing path: endpoint, state processor, handler, repository, message handler, hook, component, or adapter.
3. Reuse local patterns instead of introducing a new structure during a bug fix.
4. Add one regression test for the reproduced scenario.
5. Add extra coverage only for closely related edge cases.
6. Verify with the commands exposed by `Makefile` and the frontend package scripts when relevant.

Avoid:
- mixing unrelated cleanup into the fix
- speculative refactors during an urgent bug fix
- skipping the regression test unless the repo truly cannot express one
- introducing extra indirection for a local issue
