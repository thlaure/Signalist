Implement a new Signalist feature by mirroring the local repository patterns.

User request: `$ARGUMENTS`

Execution order:
1. Run the equivalent of `/symfony:scan-project` if context is incomplete.
2. Find one nearby example in the same backend or frontend area and mirror its structure.
3. Implement the smallest coherent slice that satisfies the request.
4. Write tests in the same session.
5. Run the repository quality gates before reporting completion.

Default expectations unless the repo clearly differs:
- Follow SOLID principles pragmatically.
- Prefer clean architecture and hexagonal boundaries when the surrounding code already uses them.
- If API Platform already provides a direct, readable solution for the requested backend behavior, use the API Platform feature instead of adding extra layers.
- If the existing frontend pattern already solves the behavior cleanly, reuse it instead of introducing a new client abstraction.
- Keep API Platform resources, processors, controllers, hooks, and components thin.
- Keep business logic in handlers, use-cases, domain services, or message handlers.
- Keep repositories and infrastructure adapters focused on persistence or integration concerns.
- Keep async boundaries explicit for RSS, AI, email, and sync work.
- Prefer simple, readable code over clever or highly optimized code.
- If performance and readability conflict and there is no measured bottleneck, choose readability.
- Keep the result easy for a human reviewer to follow.

Checklist:
1. Confirm the target flow: API Platform native, handler-based write flow, async message flow, frontend flow, or a combination.
2. Reuse existing naming and file placement conventions.
3. Keep `declare(strict_types=1);` and modern PHP syntax where relevant.
4. Add or update validation at the input boundary.
5. Keep exceptions, HTTP error mapping, and frontend error behavior aligned with the existing project.
6. Add the right tests:
   - backend unit tests for behavior and orchestration
   - integration tests when persistence or adapter behavior changes
   - Behat tests when endpoint behavior changes
   - frontend tests when UI behavior changes
7. Verify with the commands exposed by `Makefile` and `frontend/package.json`.

Avoid:
- business logic in controllers, state processors, or large components
- new dependencies without explicit approval
- schema changes without explicit approval
- adding hexagonal or CQRS indirection when API Platform or the existing frontend stack can solve the case directly and cleanly
- premature optimization or indirection that hurts readability
- project reshaping when the request only needs a local change
