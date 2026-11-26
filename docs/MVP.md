# Signalist weekly digest MVP

Promise: your RSS sources, one weekly email with links to explore further.
Audience: individual freelancers, consultants and creators.

## Delivered surface

- Verified signup/login, personal RSS/Atom sources, weekly digest history, subscription, privacy/settings.
- Free: 3 active sources. Premium: 20. The summary model and quality are identical.
- Monday 08:00 UTC. Articles collected and, when supplied, published in the previous seven days. No usable new articles means an archived empty edition and no email.
- No arbitrary web-page extraction, daily editions, workspaces, chat, discovery or social integrations.
- Legacy data/code is preserved. In default MVP mode the server rejects excluded endpoints and skips excluded queued jobs. The frontend exposes only the MVP pages. Existing legacy regression tests run with server MVP mode disabled; dedicated MVP tests enable the real request guard.

## API

Existing `/api/v1/auth/*` and `/api/v1/user/*` retain account and privacy behavior.

| Method | Path | Behavior |
| --- | --- | --- |
| GET, POST | `/api/v1/sources` | List sources/quota, add RSS URL; fetching is asynchronous |
| DELETE | `/api/v1/sources/{id}` | Delete owned source/articles, retain archived digests |
| PUT | `/api/v1/sources/selection` | Select active owned source IDs within quota |
| GET | `/api/v1/digests` | History, inference setup state, next weekly delivery |
| GET | `/api/v1/digests/{id}` | Owned immutable edition and citations |
| GET | `/api/v1/billing` | Current plan, quota, setup state and offers |
| POST | `/api/v1/billing/checkout` | Queue individual subscription checkout |
| POST | `/api/v1/billing/portal` | Queue subscription portal |
| GET | `/api/v1/billing/links/{id}` | Poll the queued Stripe link |
| POST | `/api/v1/billing/webhook` | Verify signature and queue reconciliation |

## Processing and failure handling

`SourceController -> SourceHandler -> Feed -> CrawlFeedMessage` reuses the existing RSS parser and SSRF protections. Collection is refreshed hourly through Scheduler/Messenger. A crawl processes at most 100 entries; per-user daily ingestion is capped at 100 articles. A PostgreSQL advisory lock serializes crawls and repeated digest deliveries. Duplicate GUIDs within one RSS response are ignored too.

`DispatchWeeklyDigestsMessage -> WeeklyDigest -> GenerateWeeklyDigestMessage` creates a durable outbox. A unique owner/period constraint prevents duplicate editions. The default selects up to 2 usable articles per source and 8 total. Cleaned input is at most 2,000 characters per article, with a validated 20,000-character total ceiling. At most 3 inference attempts per edition, spaced by a 15-minute dispatch lease (`dispatched_at`) so each digest is queued at most once per window and Messenger retries never consume attempts; the provider output is capped at 1,600 tokens and each stored summary at 1,000 characters. These are hard workload bounds, not a currency spending guarantee for arbitrary future providers.

URLs are deduplicated within an edition and against the latest 100 earlier editions. Citation URLs come from stored articles, never model output. Archived items survive source removal. Snapshot contents are escaped in email and rendered as text in React.

Only consents granted before the weekly cutoff qualify for that edition; activation during the week starts with the following Monday. Explicit AI and newsletter consents, verified email and unrestricted processing are checked before inference and before email. Empty weeks skip inference and SMTP. Missing inference configuration is visible as `setup_pending`; no fake production summaries. Interrupted inference retries within the stored attempt budget. Only the current week is processed: unfinished editions of earlier weeks become `skipped` and are never delivered late. Persisted summaries are reused after a crash. SMTP starts only after persisting `sending`; a crash/failure becomes `uncertain`, with no automatic resend. The archive remains readable.

### Digest status lifecycle

Normal path: `queued` → `generating` → `ready` → `sending` → `sent`.

| Status | Meaning | Next step |
| --- | --- | --- |
| `queued` | Created for the week, or waiting to retry after a failed generation | Picked up when its 15-minute dispatch lease is free |
| `generating` | AI call in progress (one attempt used) | `ready`, back to `queued`, or `failed` |
| `ready` | Summaries saved, email not started | `sending` |
| `sending` | Email being sent | `sent`, or `uncertain` after a failure or a crash older than 10 minutes |
| `sent` | Delivered to the mail transport | Final |
| `empty` | No usable new articles this week; no AI call, no email | Final |
| `setup_pending` | Local AI not configured | Resumes in the same week once configured |
| `selection_required` | More active sources than the plan allows | Resumes in the same week once the user selects sources |
| `cancelled` | Consent withdrawn, email unverified or processing restricted | Final |
| `failed` | 3 inference attempts used | Final |
| `uncertain` | Email may or may not have been delivered; never resent automatically | Final; the edition stays readable in the app |
| `skipped` | Week ended before the edition could be finished | Final; old editions are never sent late |

After subscription cancellation or entitlement expiry, all sources and digests remain. If selected sources exceed the free limit, collection and generation stop until the user chooses active sources. No automatic deletion or arbitrary source selection.

## Local setup and external services

Apply the additive migrations with `make db-migrate` (`Version20261017000100`, `…000200` and `…000300`, which adds the `dispatched_at` lease column). Keep the existing app, Messenger and `scheduler_signalist` consumer running. No container rebuild is needed for mounted source changes.

Defaults are configurable in `config/services.yaml` or through runtime environment:

| Variable | Default |
| --- | --- |
| `SIGNALIST_MVP_ENABLED` | true |
| `SIGNALIST_FREE_SOURCES` | 3 |
| `SIGNALIST_PAID_SOURCES` | 20 |
| `DIGEST_ARTICLES_PER_SOURCE` | 2 |
| `DIGEST_ARTICLES_MAX` | 8 |
| `DIGEST_CHARACTERS_MAX` | 2000 |
| `DIGEST_ATTEMPTS_MAX` | 3 |
| `DIGEST_AI_URL`, `DIGEST_AI_MODEL` | unset: visible setup-pending state |
| `FRONTEND_URL` | existing application URL, used for email preferences |
| `PUBLIC_APP_URL` | landing build: http://localhost:5173 |

Source quotas accept 1–20. Inference limits reject unsupported budgets instead of silently increasing costs.

The supplied inference adapter uses a **local Ollama** `/api/generate` endpoint, with a configured installed model, JSON output and streaming disabled. It accepts only localhost, loopback or `host.containers.internal`; sending feed text to cloud AI is not enabled. No model is downloaded and no paid inference runs during verification. Account IDs, emails and source URLs are excluded from prompts; existing cleaning/anonymization is reused. OpenAI setup remains deferred as requested. See [Ollama generation contract](https://docs.ollama.com/api/generate).

Stripe uses the existing `STRIPE_SECRET_KEY`, `STRIPE_TEAM_PRICE_ID` (the single individual Premium price), and `STRIPE_WEBHOOK_SECRET`. By default the gateway accepts **test keys only**. Live activation requires an explicit operational configuration change; no live payment is performed. The real price/interval is defined in Stripe and shown before checkout confirmation. Unknown pricing is not advertised as a fixed offer.

Mailer uses existing `MAILER_DSN`; local verification uses Mailpit or mocked/null transports. A real provider/model, Stripe test setup and validated email delivery must be configured before live onboarding. Production deployment and paid activation are separate from this local implementation.

## Verification

Run `make quality`, `make tests`, `make tests-api`, `make front-lint`, `make front-test`, frontend `npm run typecheck`, `make front-build`, and `make landing-build`. Dedicated tests cover quotas, downgrade selection, owner isolation, excluded endpoints/jobs, weekly boundaries, citations, consent withdrawal, empty weeks, inference retries and uncertain delivery.

### Verified in this implementation

Last full backend run on 2026-11-21 (after the dispatch lease and stale-week fixes):

- PHP CS Fixer, PHPStan level 9 and Rector: passed.
- `make tests`: 224 tests, 932 assertions passed.
- `make tests-api`: 86 scenarios, 647 steps passed.

Earlier run for the initial MVP delivery:

- Frontend: 46 tests, ESLint, TypeScript and production build passed.
- Landing production build passed.
- Full `doctrine:schema:validate` still reports pre-existing category/feed partial-index normalization and the unmanaged pgvector cosine index. No generated drop/recreate SQL was applied to those indexes.

Not yet verified:

- `Version20261017000300` is not yet applied to the local development database.
- Visual browser QA of the Sources, Digests and Billing pages.
- Real inference with an installed Ollama model, Stripe test checkout and real email delivery. No paid inference, live charge or external email has been performed.

## Known limitations and open decisions

- The Ollama adapter accepts `localhost`, `127.0.0.1`, `[::1]` and `host.containers.internal` only. Docker Desktop's `host.docker.internal` is rejected, so Docker users must adjust the network setup or the allowlist.
- Registration requires 8 characters but no longer checks password strength. Confirm this is an accepted trade-off.
- `StripeBillingGateway::$allowLive` is not wired to an environment variable; enabling live keys needs a configuration change in `services.yaml`.
- The weekly window uses article collection time, so an article published last week but first crawled after Monday 08:00 UTC is not included.
- Legacy features stay in the codebase and keep their tests. Decide whether to delete them once the digest is validated.
