# ADR-004: JWT Authentication

## Status

Accepted

## Context

Signalist is a SPA (React frontend) communicating with a Symfony API backend. We evaluated authentication strategies:

1. **Session-based auth** — traditional PHP sessions with cookies. Simple but requires sticky sessions and doesn't scale well for API-first architectures.
2. **JWT (JSON Web Tokens)** — stateless tokens sent via `Authorization: Bearer` header. Standard for SPA + API setups.
3. **OAuth2 / OpenID Connect** — full-featured but complex for a single first-party client.

The API needs to be stateless, support easy frontend integration, and eventually serve MCP endpoints for LLM ecosystem access.

## Decision

We use **LexikJWTAuthenticationBundle** for stateless JWT authentication:

- Login endpoint (`POST /api/v1/auth/login`) validates credentials and returns a signed JWT + expiration
- All `/api/v1/*` endpoints (except auth routes) require a valid `Authorization: Bearer <token>` header
- Tokens are signed with RS256 (asymmetric keys stored in `config/jwt/`)
- Token payload includes user email and roles
- Email verification is enforced at login time (unverified users get a 403)

## Consequences

**Positive:**
- Fully stateless — no server-side session storage needed
- Standard Bearer token pattern that any HTTP client can use
- Easy to extend to MCP and third-party integrations
- Works naturally with SPA frontends (stored in memory, not cookies)

**Negative:**
- Tokens cannot be revoked server-side without a blocklist (acceptable for MVP)
- Token refresh mechanism not yet implemented (planned for Phase 2)
- JWT payload size increases with claims (currently minimal)
