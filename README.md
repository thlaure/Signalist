# Signalist

**Your RSS sources, summarized into one weekly email.**

Signalist collects the RSS/Atom feeds you choose, writes a short AI summary of the
best new articles, and sends one digest every Monday morning — with links to
read further. Every edition is archived in the app.

Audience: individual freelancers, consultants and creators who follow a handful
of sources and want a weekly signal instead of a daily inbox.

> **Status:** local MVP, not deployed. Real AI summaries, Stripe checkout and
> browser QA have not been validated yet. See [Project status](#project-status).

---

## Features (MVP edition)

| Area | What the user gets |
|------|--------------------|
| **Account** | Sign up with email verification, JWT login, password of at least 8 characters |
| **Sources** | Add RSS/Atom URLs (validated, SSRF-protected), remove them, choose which ones are active |
| **Weekly digest** | Every Monday 08:00 UTC: up to 2 articles per source, 8 in total, from the previous 7 days, each with a short summary and its original link |
| **History** | All past editions readable in the app, even after a source is removed |
| **Plans** | Free: 3 active sources. Premium: 20. Same summary quality on both |
| **Billing** | Stripe checkout and customer portal for the individual Premium plan |
| **Privacy** | Explicit consents (AI processing, newsletter), JSON export, processing restriction, account deletion |
| **Languages** | English and French (app and landing page) |

What the MVP intentionally does **not** do: daily editions, full web-page
extraction, team workspaces, social publishing, chat/Q&A, search, discovery.

### Legacy features (disabled by default)

The codebase also contains an earlier, broader "team news curation" product:
categorized inboxes, bookmarks and tags, semantic search, newsletters,
workspaces with roles, Raindrop sync, social publishing (LinkedIn, X, Bluesky),
Spotlight and a read-only MCP server.

These are kept but switched off while `SIGNALIST_MVP_ENABLED=true` (the
default): their API routes return 404, `/mcp` is closed, their background jobs
are skipped, and the frontend only shows the MVP pages. Set
`SIGNALIST_MVP_ENABLED=false` to restore them. Their test suites still run with
MVP mode disabled.

---

## How the weekly digest works

```
User adds source ─► CrawlFeedMessage ─► RSS fetch (hourly refresh, ≤100 entries/crawl)
                                              │
Scheduler (every minute) ─► DispatchWeeklyDigestsMessage
   ├─ creates one WeeklyDigest per eligible user and week (unique owner+period)
   ├─ queues GenerateWeeklyDigestMessage (15-minute lease per digest)
   └─ skips unfinished editions of earlier weeks
                                              │
GenerateWeeklyDigestMessageHandler
   ├─ picks recent articles, cleans and anonymizes text
   ├─ local Ollama summarizes them (max 3 attempts)
   ├─ saves the edition, marks it "sending"
   └─ sends the email (never automatically resent if delivery is uncertain)
```

Key safety rules:

- A user is included only with verified email, unrestricted processing, and
  both `newsletter` and `external_ai` consents granted **before** that week's
  cutoff. Consent is re-checked before the AI call and before sending.
- Links in the email come from stored articles, never from AI output. Article
  text is treated as untrusted input in the prompt; account IDs, emails and
  source URLs are never sent to the model.
- AI runs on a **local** Ollama instance only (localhost / loopback /
  `host.containers.internal`). Without it, digests show "AI setup pending" —
  no fake summaries.
- A week with no usable new articles produces an archived empty edition and no
  email.

Full rules, limits and the API reference: [docs/MVP.md](docs/MVP.md).

---

## Tech stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.5 · Symfony 8.1 · API Platform 4.4 · FrankenPHP |
| **Frontend** | React 19 · TypeScript · Vite 7 · MUI 7 · React Query · react-i18next |
| **Database** | PostgreSQL 16 · pgvector (legacy search) |
| **Queue** | Symfony Messenger · Scheduler · Redis |
| **AI** | Local Ollama for digests (provider-independent ports elsewhere) |
| **Email** | Symfony Mailer (Mailpit locally) |
| **Payments** | Stripe (test keys only by default) |
| **Landing** | Astro 7 · Tailwind v4 |
| **Auth** | LexikJWT |
| **Testing** | PHPUnit · Behat · Vitest · React Testing Library |

## Architecture

Hexagonal architecture with CQRS-style handlers:

```
Controller ─► Input DTO ─► Handler (business rules) ─► Doctrine / Port
                                 │
                                 └─► Messenger message ─► MessageHandler ─► RSS / AI / Mail / Stripe
```

Slow work (RSS crawling, AI, email, Stripe) always runs asynchronously. Errors
follow RFC 7807 problem details. The digest feature lives in
`src/Domain/Digest/`; its HTTP entry points are `SourceController` and
`DigestController` in `src/UI/Controller/`.

---

## Getting started

### Prerequisites

- Docker & Docker Compose (Podman with `podman compose` also works)
- Make
- Node 22.19+ for the frontend and landing page (Node 24 recommended)

### Install and run

```bash
git clone https://github.com/thlaure/Signalist.git
cd Signalist
make install        # build + start containers, install deps, run migrations
docker compose exec app php bin/console lexik:jwt:generate-keypair   # first time only
make front-dev      # React app on http://localhost:5173
```

`make up` / `make down` start and stop all services (app, messenger worker,
scheduler, Postgres, Redis, Mailpit).

- API: `http://localhost:8000` · Mailpit: `http://localhost:8025`
- After pulling new code, run `make db-migrate`.
- The `messenger` and `scheduler` services must be running for crawls and
  digests to happen.

### Enable real summaries (optional)

Install [Ollama](https://ollama.com), pull a model, then set:

```bash
DIGEST_AI_URL=http://host.containers.internal:11434
DIGEST_AI_MODEL=<installed model name>
```

The adapter only accepts `localhost`, `127.0.0.1`, `[::1]` and
`host.containers.internal`. Inside a container, `localhost` is the container
itself, so with Podman use `host.containers.internal`. Docker Desktop's
`host.docker.internal` is **not** accepted yet: run Ollama in the same network
namespace or extend the allowlist in `OllamaDigestIntelligence`.

### Configuration

| Variable | Default | Purpose |
|----------|---------|---------|
| `SIGNALIST_MVP_ENABLED` | `true` | Weekly digest edition; `false` restores legacy features |
| `SIGNALIST_FREE_SOURCES` / `SIGNALIST_PAID_SOURCES` | `3` / `20` | Active source quotas (1–20) |
| `DIGEST_ARTICLES_PER_SOURCE` / `DIGEST_ARTICLES_MAX` | `2` / `8` | Articles per edition |
| `DIGEST_CHARACTERS_MAX` / `DIGEST_ATTEMPTS_MAX` | `2000` / `3` | AI input size and retry budget |
| `DIGEST_AI_URL` / `DIGEST_AI_MODEL` | unset | Local Ollama endpoint and model |
| `STRIPE_SECRET_KEY`, `STRIPE_TEAM_PRICE_ID`, `STRIPE_WEBHOOK_SECRET` | unset | Premium billing (test keys only) |
| `MAILER_DSN`, `SIGNALIST_MAIL_SENDER` | Mailpit / `noreply@signalist.app` | Email delivery |

Production variables are listed in [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

---

## Useful commands

| Command | Description |
|---------|-------------|
| `make quality` | PHP CS Fixer + PHPStan (level 9) + Rector |
| `make tests` | All PHPUnit tests (unit + integration) |
| `make tests-api` | Behat API scenarios |
| `make front-lint` / `make front-test` | ESLint / Vitest |
| `make front-build` / `make landing-build` | Production builds |
| `make grumphp` | Full pre-commit suite |
| `make worker-failed` | Inspect failed Messenger jobs |
| `make help` | All commands |

Backend test suites share one disposable database: run them one at a time, not
in parallel.

---

## Project status

**Done locally:** MVP backend, frontend and landing page. On 2026-11-21: 224
PHPUnit tests, 86 Behat scenarios, frontend tests, PHPStan, CS Fixer and Rector
all pass.

**Not yet validated:**

- Real AI summaries with an installed Ollama model
- Stripe checkout with test credentials
- Visual browser QA of the new pages
- Real email delivery and production deployment

**Open decisions:**

- Positioning: the earlier plan targeted small curation teams; the MVP targets
  individuals. Pick one and keep the docs aligned.
- Registration no longer enforces password strength (8 characters minimum only).
- Enabling live Stripe keys needs a code/config change (`allowLive` is not wired
  to an environment variable).
- Whether to delete the legacy features once the digest is validated.

---

## Documentation

| Document | Content |
|----------|---------|
| [docs/MVP.md](docs/MVP.md) | Weekly digest scope, API, processing rules, limits, verification |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Local setup details, production topology, external accounts |
| [docs/PRIVACY.md](docs/PRIVACY.md) | Implemented privacy controls and operator checklist |
| [docs/ROADMAP.md](docs/ROADMAP.md) | Feature history, including legacy features |
| [CHANGELOG.md](CHANGELOG.md) | Notable changes by milestone |
| [CONTRIBUTING.md](CONTRIBUTING.md) | Branches, commits, conventions, tests, pull requests |
| [REVIEW.md](REVIEW.md) | Code review priorities and checklist |
| [SECURITY.md](SECURITY.md) | Vulnerability reporting, controls in place, known gaps |
| [AGENTS.md](AGENTS.md) | Rules for coding agents |
| `docs/IMPLEMENTATION-PLAN.md`, `docs/PR-NOTES.md`, `docs/BROWSER-QA.md` | Historical records of the October 2026 roadmap delivery |

---

## Contributing

This project is in active development. Contributions are not open yet; the
working conventions are in [CONTRIBUTING.md](CONTRIBUTING.md). Report security
issues privately as described in [SECURITY.md](SECURITY.md).

## License

Private — all rights reserved.
