# ADR-002: API Platform with Custom State Providers

## Status

Accepted

## Context

Signalist exposes a REST API for its frontend and future integrations. We evaluated two approaches:

1. **Symfony controllers** with manual request/response handling, validation, and serialization
2. **API Platform 4** with custom State Providers and Processors wired to our CQRS layer

Symfony controllers give full control but require boilerplate for content negotiation, OpenAPI documentation, pagination, and error formatting. API Platform provides these out of the box but typically expects Doctrine entities as API resources.

## Decision

We use **API Platform 4** with **dedicated Resource classes** (not Doctrine entities) and **custom StateProvider/StateProcessor** classes that delegate to our CQRS Handlers.

- Each domain has a `*Resource` class (e.g., `ArticleResource`) that is a plain readonly DTO
- `StateProvider` reads data by invoking Query Handlers
- `StateProcessor` writes data by invoking Command Handlers
- Input DTOs (`*Input` classes) handle request validation via Symfony constraints
- Output mapping is done via `*Output::fromEntity()` static factories

## Consequences

**Positive:**
- Automatic OpenAPI/Swagger documentation generation
- Clean separation: API Platform handles HTTP concerns, Handlers handle business logic
- Input validation via Symfony Validator on dedicated DTO classes
- Resource classes are decoupled from Doctrine entities

**Negative:**
- Custom providers/processors add a layer of indirection vs. direct controller calls
- API Platform's conventions require understanding its lifecycle (provider → processor flow)
- Login endpoint still uses a traditional Symfony controller (API Platform doesn't fit auth flows)
