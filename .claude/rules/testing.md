# Testing Rules

- Tests are part of delivery, not follow-up work.
- Bug fixes should ship with a regression test whenever the behavior can be reproduced in an automated way.
- Choose test scope based on the changed behavior:
  - backend unit tests for handlers, domain services, message handlers, and validators
  - backend integration tests for persistence behavior, Doctrine wiring, and runtime adapters
  - Behat tests for endpoint behavior and API-level contracts
  - frontend Vitest/RTL tests for component behavior, hooks, and interaction flows
- When endpoint behavior changes, cover both the happy path and at least one failure path when practical.
- When frontend behavior changes, cover the user-visible interaction or state transition instead of only implementation details.
- Keep test naming consistent with the repository convention.
- Run the narrowest relevant tests first, then the broader repository checks.
- If a behavior changes and no test is added or updated, explain why explicitly.
