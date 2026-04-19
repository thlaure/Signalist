# Architecture Rules

- Read `AGENTS.md` first. It is the canonical source of repository-specific rules.
- Apply SOLID pragmatically.
- Prefer clean architecture, hexagonal boundaries, and CQRS when the surrounding area already uses them.
- If API Platform already supports the required backend behavior cleanly, prefer the built-in API Platform feature over extra layers.
- If the existing frontend stack already supports the behavior cleanly through React Query, React Router, or a local hook pattern, prefer that over a new abstraction.
- Keep API Platform resources, state processors, controllers, and components thin. They should orchestrate, not decide.
- Keep business rules in handlers, domain services, or message handlers.
- Keep repositories and infrastructure adapters focused on persistence, transports, RSS, AI providers, or third-party integrations.
- Keep async boundaries explicit for RSS crawling, AI generation, newsletter scheduling, and other slow side effects.
- Do not introduce blocking outbound work in the request cycle when the repo already models it asynchronously.
- Mirror the local project shape instead of forcing a generic Symfony-only layout onto backend and frontend code.
- Prefer incremental changes that reuse existing conventions over broad restructuring.
- Optimize first for readability, reviewability, and testability.
- If there is no measured performance issue, prefer the simpler and more readable solution.
- The final code should be easy for a human reviewer to understand quickly.
