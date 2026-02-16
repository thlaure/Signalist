# ADR-005: User-Scoped Data via Owner Relationship

## Status

Accepted

## Context

Signalist is a multi-user SaaS application where each user has their own feeds, categories, articles, and bookmarks. We evaluated data isolation strategies:

1. **Multi-tenant with separate databases** — strong isolation but complex infrastructure, overkill for our scale
2. **Multi-tenant with schema separation** — moderate isolation, PostgreSQL schema support, adds migration complexity
3. **Row-level ownership** — each entity has an `owner` foreign key to the User table, filtered at query time

## Decision

We use **row-level ownership** with an `owner` (ManyToOne to User) relationship on Category and Feed entities:

- Categories and Feeds have a direct `owner` relationship
- Articles inherit ownership through their Feed (`article → feed → owner`)
- Bookmarks inherit ownership through their Article (`bookmark → article → feed → owner`)
- Every repository query filters by `ownerId` to prevent cross-user data leakage
- API Platform State Providers inject the authenticated user's ID into every Query/Command
- Unique constraints include `owner_id` (e.g., `uniq_category_slug_owner`, `uniq_feed_url_owner`)

## Consequences

**Positive:**
- Simple to implement and reason about — standard foreign key relationships
- Single database, single schema — no infrastructure complexity
- Unique constraints are per-user (two users can have the same category slug)
- Easy to add user data export (GDPR) by querying all owned entities

**Negative:**
- Every query must include the owner filter — forgetting it leaks data (mitigated by always passing `ownerId` through the CQRS layer)
- No database-level enforcement of row isolation (application-level only)
- JOIN-based ownership checks for Articles and Bookmarks add query complexity
