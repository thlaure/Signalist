# Signalist

**AI-powered news curation and publishing platform for small teams.**

Signalist helps news curators, content teams, and media monitors stay on top of
their industry — aggregating RSS feeds, synthesizing content with AI, and
publishing insights across newsletters and social media.

---

## What it does

**Monitor → Curate → Synthesize → Publish**

- **Aggregate** RSS feeds into categorized, searchable inboxes
- **Summarize** articles automatically with LLMs (OpenAI, Anthropic, Mistral)
- **Bookmark & tag** content with AI-assisted auto-tagging
- **Generate newsletters** from curated articles, configurable by reading time
- **Publish** to social media (X, LinkedIn, Threads, Bluesky, WhatsApp)
- **Search** with full-text and semantic (vector) search

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| **Backend** | PHP 8.5 · Symfony 7.4 · API Platform 4.x · FrankenPHP |
| **Frontend** | React 19 · TypeScript · Vite 7 · MUI v7 · react-i18next |
| **Database** | PostgreSQL 16 · pgvector |
| **Queue** | Symfony Messenger · Redis |
| **AI** | Symfony AI (OpenAI / Anthropic / Mistral) |
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
