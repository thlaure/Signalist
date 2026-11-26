# Changelog

All notable changes to Signalist are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).
Versions before 0.5.0 were assigned retroactively to past delivery milestones.

## [Unreleased]

The product is refocused on a personal weekly RSS digest for individual users.
Earlier team features stay in the codebase but are disabled by default.

### Added

- Weekly digest: every Monday 08:00 UTC, up to 2 articles per source and 8 in
  total from the previous 7 days, each with a short AI summary and its original
  link, sent by email and archived in the app.
- `WeeklyDigest` entity and migrations (`Version20261017000100`, `…000200`,
  `…000300`) with one edition per user and week.
- Personal source API: `GET/POST /api/v1/sources`, `DELETE /api/v1/sources/{id}`,
  `PUT /api/v1/sources/selection`.
- Digest API: `GET /api/v1/digests`, `GET /api/v1/digests/{id}`.
- Personal billing endpoints: `POST /api/v1/billing/checkout` and
  `POST /api/v1/billing/portal`.
- Local Ollama adapter (`OllamaDigestIntelligence`) restricted to loopback and
  `host.containers.internal`, with bounded input, output and attempts.
- 15-minute dispatch lease per digest so slow workers cannot pile up duplicate
  jobs and failed generations retry with spacing.
- Digest statuses `setup_pending`, `selection_required`, `empty`, `uncertain`
  and `skipped`, shown in the app in English and French.
- `MvpScopeListener`: returns 404 for legacy API routes and `/mcp`, and skips
  legacy background jobs while `SIGNALIST_MVP_ENABLED=true`.
- Frontend pages: Sources, Digests, Digest detail; simplified Billing page.
- Configuration variables `SIGNALIST_MVP_ENABLED`, `SIGNALIST_FREE_SOURCES`,
  `SIGNALIST_PAID_SOURCES`, `DIGEST_*`.
- Documentation: `docs/MVP.md`, rewritten `README.md`, `CHANGELOG.md`,
  `SECURITY.md`, `CONTRIBUTING.md`, `REVIEW.md`.

### Changed

- Plans: Free 3 active sources, Premium 20 (configurable 1–20); same summary
  quality on both. Daily article intake capped at 100 on every plan.
- Crawling: at most 100 entries per crawl, duplicate GUIDs within one response
  ignored, one crawl per feed at a time, failed fetches still record their time.
- Consent checks can require consent granted before a cutoff (weekly edition).
- Stripe billing reports itself as configured only with `sk_test_` keys unless
  live mode is explicitly enabled.
- Frontend routes reduced to Sources, Digests, Billing and Settings; sidebar
  simplified.
- Landing page: calls to action link to registration instead of the waitlist;
  pricing shows two plans.
- Privacy export and account deletion include weekly digests.
- Scheduler in MVP mode runs digest, billing and retention jobs only.
- Upgraded to Symfony 8.1, Doctrine DBAL 4, DoctrineBundle 3, Doctrine
  Migrations Bundle 4, API Platform 4.4, `mcp/sdk` 0.10 and Behat 4.
- Runtime moved to FrankenPHP 1.14.0 with PHP 8.5.13; the custom FrankenPHP
  build stage is gone.
- Behat configuration moved from `behat.yaml.dist` to `behat.dist.php`, and
  step definitions use attributes.
- Rector uses composer-based sets matching the installed Symfony, Doctrine,
  PHPUnit and Twig versions.

### Removed

- Password strength score check at registration (minimum length of 8
  characters remains).
- `runtime/frankenphp-symfony` (unused: FrankenPHP does not run in worker mode)
  and `enlightn/security-checker` (replaced by `composer audit` in GrumPHP).
- DBAL 3 type comments (`DC2Type`) on database columns.

### Fixed

- A failed digest generation no longer rethrows, so Messenger retries cannot use
  the whole attempt budget within seconds.
- Unfinished editions from earlier weeks are marked `skipped` instead of being
  emailed late once AI is configured.
- Production container now declares the Doctrine query and result cache pools
  it references.

### Security

- Patched dependencies flagged by new advisories (Symfony, `mcp/sdk`, undici,
  follow-redirects, Alpine and Go packages in the runtime image). zod
  GHSA-mrhx-c7xc-m3w5 in the legacy Bluesky adapter is accepted until
  `@atproto` supports zod 4.

## [0.4.0] - 2026-10-13

### Added

- Privacy lifecycle: consent, export, processing restriction, retention,
  password-confirmed deletion, encrypted external credentials.
- Provider-independent AI pipelines: summaries, tags, `vector(1536)` embeddings,
  full-text and semantic search, sourced Q&A.
- Tags, newsletters with scheduling, shared workspaces with roles and
  annotations, Stripe billing with seats and webhooks.
- Raindrop.io bookmark sync; LinkedIn, X and Bluesky drafts and publishing.
- Spotlight command palette and an authenticated read-only MCP server.
- Confirmed, withdrawable waitlist and privacy pages on the landing site.
- Production Docker topology, deployment guide and stricter CI gates.

### Changed

- Dependencies refreshed; abandoned PHP packages replaced; landing upgraded to
  Astro 7.
- Frontend pages and the authenticated layout load on demand.

### Fixed

- Test database constraints reset before schema creation.
- FrankenPHP rebuilt with a patched HTTP/2 dependency.

## [0.3.0] - 2026-04-19

### Added

- Astro + Tailwind landing page in English and French.
- Trivy container and filesystem scans in CI.
- Agent instructions moved to `AGENTS.md` with shared skills.

### Fixed

- Production image builds (`APP_ENV=prod`, Composer scripts, `var/` permissions).
- High-severity npm audit findings.

## [0.2.0] - 2026-03-15

### Added

- English/French translations, article pagination, unread filter, feed
  navigation, category filter on feeds.
- Security audit steps in CI.

### Security

- SSRF protection for feed URLs.
- User enumeration prevented at registration; password strength enforced
  (later relaxed in the weekly digest MVP).
- Article HTML sanitized with DOMPurify.
- Internal errors no longer leak in API responses.

## [0.1.0] - 2026-02-16

### Added

- Feed, Category, Article and Bookmark domains with asynchronous RSS crawling.
- JWT login, registration and email verification.
- All data scoped to the authenticated user.
- React + Vite + MUI frontend with protected routes.
- Initial Symfony + FrankenPHP setup and project documentation.
- Behat API tests, data fixtures, Swagger UI, GrumPHP, Rector.

[unreleased]: https://github.com/thlaure/Signalist/compare/v0.4.0...HEAD
[0.4.0]: https://github.com/thlaure/Signalist/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/thlaure/Signalist/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/thlaure/Signalist/compare/v0.1.0...v0.2.0
[0.1.0]: https://github.com/thlaure/Signalist/releases/tag/v0.1.0
