---
name: new-feature
description: Use this skill when the user asks to add, create, implement, or build new functionality in Signalist.
---

# New Feature

Use this skill to implement new functionality while staying aligned with the local project architecture.

Read first:
- `AGENTS.md`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`
- `.claude/rules/security.md`

Read as needed:
- `.claude/patterns.md`
- nearby files in the same backend or frontend area

Workflow:
1. If context is incomplete, inspect the repo shape and find a nearby example first.
2. Mirror the local naming, placement, and flow instead of generating framework-default code.
3. Follow SOLID pragmatically and keep clean or hexagonal boundaries readable.
4. If API Platform already supports the requested backend behavior cleanly, use it directly instead of adding extra layers.
5. If the existing frontend stack already supports the requested UI behavior cleanly, reuse it directly instead of adding a new abstraction.
6. Keep the implementation simple and easy for a human reviewer to follow.
7. Add the right tests in the same session.
8. Run the repository quality gates before reporting completion.

Rules:
- Keep entrypoints thin.
- Keep business rules in handlers, use-cases, domain services, or message handlers.
- Keep repositories and infrastructure adapters focused on their boundary concerns.
- Prefer readability over premature optimization.
