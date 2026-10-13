# Signalist

**AI-powered news curation and publishing platform for small teams.**

Signalist helps news curators, content teams, and media monitors stay on top of
their industry — aggregating RSS feeds, synthesizing content with AI, and
publishing insights across newsletters and social media.

---

## What it does

**Monitor → Curate → Synthesize → Publish**

- **Aggregate** RSS feeds into categorized, searchable inboxes
- **Enrich** articles through a provider-independent AI port (live provider setup deferred)
- **Bookmark & tag** content, with owner-scoped tag management and optional AI tagging
- **Generate newsletters** from curated articles, configurable by reading time
- **Share** links to X, LinkedIn, Threads, Bluesky and WhatsApp; approve API publishing to configured LinkedIn, X and Bluesky accounts
- **Search** with full text; semantic/hybrid modes become available with a configured embedding provider
- **Collaborate** in workspaces with roles, annotations and independent read status
- **Control privacy** with consent, export, restriction and password-confirmed deletion
- **Manage plans** through configured Stripe checkout, portal and signed webhooks

---

Implementation and release configuration: [deployment guide](docs/DEPLOYMENT.md). External provider integrations are disabled until credentials and account approvals are supplied. Production deployment and live provider validation remain outstanding.

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.5 · Symfony 7.4 · API Platform 4.x · FrankenPHP |
| **Frontend** | React 19 · TypeScript · Vite 7 · MUI v7 · react-i18next |
| **Database** | PostgreSQL 16 · pgvector |
| **Landing** | Astro 7 · Tailwind v4 · Node 22.19+ |
| **Queue** | Symfony Messenger · Redis |
| **AI** | Provider-independent adapters; live provider setup deferred |
| **Auth** | LexikJWT |
| **Testing** | PHPUnit · Behat · Vitest · React Testing Library |

---

## Architecture

Hexagonal Architecture + CQRS + DDD

```
Request → Controller → InputDTO → Command/Query → Handler → Repository
                                                          ↓
                                                   Messenger (async)
                                                          ↓
                                              AI / RSS / External APIs
```

---

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Make

### Install

```bash
git clone https://github.com/thlaure/Signalist.git
cd Signalist
make install
```

Or manually:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php bin/console lexik:jwt:generate-keypair
docker compose exec app php bin/console doctrine:migrations:migrate
```

The app is available at `http://localhost:8000`.

### Useful commands

| Command | Description |
|---------|-------------|
| `make lint` | PHP CS Fixer |
| `make analyse` | PHPStan (level 9) |
| `make rector` | Rector modernization |
| `make quality` | All quality checks |
| `make tests-unit` | PHPUnit unit tests |
| `make grumphp` | Full pre-commit suite |
| `make help` | All available commands |

---

## Roadmap

1. **Phase 1 — MVP** ✅ Core RSS engine, auth, Inbox UI
2. **Phase 2 — Landing Page** Marketing site, waitlist, pricing
3. **Phase 3 — AI Layer** Summaries, embeddings, semantic search, auto-tagging
4. **Phase 4 — Automation** Newsletter scheduler, Raindrop.io sync
5. **Phase 5 — Team** Multi-user workspace, roles, billing
6. **Phase 6 — Ecosystem** Spotlight (`Cmd+K`), MCP server, social publishing

See [`docs/ROADMAP.md`](docs/ROADMAP.md) for the full task breakdown.

---

## Contributing

This project is in active development. Contributions are not open yet.

---

## License

Private — all rights reserved.
