# ADR-001: Hexagonal Architecture with CQRS

## Status

Accepted

## Context

Signalist is an RSS intelligence platform that aggregates feeds, applies AI processing, and serves data through a REST API. We needed an architecture that:

- Separates business logic from infrastructure concerns (database, HTTP, external APIs)
- Supports adding AI providers and external services without touching domain code
- Makes business rules independently testable without a running database or web server
- Scales to multiple delivery mechanisms (REST API, CLI, MCP server)

Traditional MVC bundles business logic into controllers and couples domain rules to the framework. Service-oriented architecture with fat services tends to produce god classes.

## Decision

We adopt **Hexagonal Architecture** (Ports & Adapters) combined with **CQRS** (Command Query Responsibility Segregation):

- **Domain layer** (`src/Domain/`) contains Commands, Queries, Handlers, DTOs, and Port interfaces
- **Infrastructure layer** (`src/Infrastructure/`) contains adapters: Doctrine repositories, API Platform providers, RSS fetchers, AI clients
- **Entity layer** (`src/Entity/`) contains Doctrine entities (persistence models)
- Handlers are the sole location for business logic
- Port interfaces define contracts; adapters implement them

CQRS splits read and write paths: Queries return data via read models, Commands mutate state via Handlers.

## Consequences

**Positive:**
- Domain logic is 100% framework-independent and unit-testable with mocks
- Adding a new delivery mechanism (e.g., MCP server) only requires a new adapter
- Achieved 93%+ test coverage on the Domain layer
- Clear dependency direction: Domain depends on nothing; Infrastructure depends on Domain

**Negative:**
- More files per feature (Command, Handler, DTO, Port, Adapter)
- Steeper onboarding curve for developers unfamiliar with the pattern
- Requires discipline to avoid leaking infrastructure concerns into Handlers
