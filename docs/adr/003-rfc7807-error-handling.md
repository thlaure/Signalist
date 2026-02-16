# ADR-003: RFC 7807 Problem Details for Error Handling

## Status

Accepted

## Context

APIs need a consistent error response format. Common approaches include:

1. **Ad-hoc JSON** — each endpoint returns errors differently, making client-side handling fragile
2. **Framework defaults** — Symfony's default exception handling returns HTML in dev and generic JSON in prod
3. **RFC 7807 Problem Details** — a standardized format with `type`, `title`, `status`, `detail`, and `instance` fields

Signalist's frontend and future integrations need predictable, machine-readable error responses.

## Decision

All API errors follow **RFC 7807 Problem Details for HTTP APIs**:

- A `ProblemException` abstract base class implements `HttpExceptionInterface` and provides `toProblemDetails()`
- Domain exceptions (e.g., `FeedNotFoundException`, `CategorySlugAlreadyExistsException`) extend `ProblemException`
- A `ProblemDetailsExceptionListener` on `kernel.exception` converts all exceptions to `application/problem+json` responses
- Validation errors include field-level details in an `errors` array extension
- Symfony's `MapRequestPayload` validation failures are caught and converted to 422 responses with the same format

## Consequences

**Positive:**
- Every error response has the same predictable structure
- Frontend can parse errors uniformly regardless of which endpoint returned them
- Problem types are URIs that can link to documentation
- Field-level validation errors are included for form handling

**Negative:**
- Every new domain exception requires defining a problem type URI
- The exception listener catches all exceptions, so internal Symfony errors are also wrapped (mitigated by showing generic messages in production)
