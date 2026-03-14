# Agent Project Guide: Signalist

---

# 0. TL;DR (Read This First)

- **Project:** AI-powered RSS intelligence platform (SaaS)
- **Architecture:** Hexagonal Architecture + CQRS + API Platform + SOLID + DDD
- **Flow:** `API Platform → State Processor → Handler → Domain Model → Repository`
- **Backend:** PHP 8.5 + Symfony 7.4 + API Platform 4.x + FrankenPHP
- **Frontend:** React 19 + TypeScript + Vite 7 + MUI v7 + react-i18next
- **Database:** PostgreSQL + **pgvector** for embeddings
- **AI:** Symfony AI (OpenAI, Anthropic, Mistral) + MCP Server
- **Queue:** Symfony Messenger + Redis
- **Testing:** PHPUnit + Vitest — mandatory
- **Code Quality:** PHP CS Fixer + PHPStan (level 9) + Rector
- **Compliance:** GDPR-compliant by design
- **Process:**
  1. **Explore** context
  2. **Plan** step-by-step & get approval (explain choices)
  3. **Implement** strictly following the plan (narrate actions)
  4. **Verify** (Lint, Analyze, Rector, Test)

## Quick Start
```bash
make install
# Or manually:
docker compose up -d --build
```

---

# 1. Project Overview

**Signalist** is a smart intelligence platform designed to:
- Aggregate, filter, and synthesize RSS feeds using AI
- Provide a natural language command interface (Spotlight-like, `Cmd+K`)
- Expose data via Model Context Protocol (MCP) for LLM ecosystem integration

## 1.1 Key Features

| Feature | Description |
|---------|-------------|
| **Feed Management** | Categories, RSS aggregation, full content extraction (Readability) |
| **Navigation** | Global dashboard, categorized views, full-text & semantic search |
| **Bookmarking** | Save articles, auto-tagging via LLM, Raindrop.io sync |
| **AI Newsletters** | LLM-generated summaries, configurable reading time (200 wpm), scheduling |
| **Social Sharing** | WhatsApp, X, LinkedIn, Threads, Bluesky integration |
| **Spotlight** | Natural language command bar (`Cmd+K`) for CRUD and AI queries |

## 1.2 Project Phases

1. **Phase 1 (MVP):** Core RSS engine, PostgreSQL schema, Inbox UI ✅
2. **Phase 2 (Landing Page):** Marketing site, waitlist, pricing
3. **Phase 3 (AI Layer):** Symfony AI for summaries and auto-tagging
4. **Phase 4 (Automation):** Newsletter scheduler, Raindrop.io sync
5. **Phase 5 (Team):** Multi-user workspace, roles, billing
6. **Phase 6 (Ecosystem):** Spotlight command engine, MCP server, social sharing

---

# 2. Tech Stack

## 2.1 Backend
- **Language:** PHP **8.5**
- **Framework:** Symfony **7.4** + **API Platform 4.x**
- **Server:** **FrankenPHP** (built on Caddy, worker mode)
- **Architecture:** CQRS, Clean Architecture, Hexagonal (Ports & Adapters)
- **Database:** PostgreSQL + **pgvector** extension
- **Queue:** Symfony Messenger + Redis
- **AI Integration:** Symfony AI (`#[AsTool]` attributes)
- **Protocol:** Model Context Protocol (MCP) Server

## 2.2 Frontend
- **Framework:** React **19** with TypeScript
- **Build:** Vite **7**
- **Components:** MUI (Material UI) **v7**
- **i18n:** react-i18next (EN default, FR supported)
- **State:** @tanstack/react-query v5
- **HTTP:** Axios
- **Testing:** Vitest + React Testing Library

## 2.3 Third-Party Integrations
- **Raindrop.io:** OAuth2 bookmark synchronization
- **Email:** Symfony Mailer
- **LLM Providers:** OpenAI, Anthropic, Mistral (via Symfony AI)

---

# 3. Commands

| Purpose | Command |
|---------|---------|
| Start containers | `docker compose up -d --build` |
| Code style | `make lint` |
| Static analysis | `make analyse` |
| Code refactoring | `make rector` |
| All quality checks | `make quality` |
| Backend unit tests | `make tests-unit` |
| All checks + tests | `make grumphp` |
| API tests | `docker compose exec app vendor/bin/behat --suite=api` |
| Frontend tests | `npm test` |
| Frontend typecheck | `npm run typecheck` |
| All commands | `make help` |

---

# 4. Architecture & File Structure

## 4.1 CQRS Components

| Component | Responsibility |
|-----------|----------------|
| **Query** | Read intent (GET). Returns DTOs via read models. |
| **Command** | Write intent (POST/PUT/DELETE). Encapsulates user intent. |
| **Handler** | Orchestrates domain logic. **Only place for business logic.** |
| **InputDTO** | Request payload validation (strict constraints). |
| **OutputDTO** | Response shaping (read-only, English field names). |
| **Controller** | HTTP Adapter. Maps Request → InputDTO → Command/Query → Response. |

## 4.2 Directory Structure

```
src/
├── Domain/                  # Business logic (vertical slices)
│   ├── Feed/
│   │   ├── Command/
│   │   ├── Query/
│   │   ├── Handler/
│   │   ├── DTO/
│   │   ├── Model/
│   │   └── Port/            # Repository interfaces
│   ├── Category/
│   ├── Article/
│   ├── Bookmark/
│   ├── Newsletter/
│   └── Spotlight/
├── Infrastructure/          # Adapters (implementations)
│   ├── Persistence/         # Doctrine repositories
│   ├── AI/                  # Symfony AI adapters
│   ├── MCP/                 # MCP server implementation
│   ├── RSS/                 # Feed parsers
│   └── External/            # Third-party APIs (Raindrop)
├── UI/                      # Controllers, CLI commands
│   ├── Controller/
│   └── Command/
└── Entity/                  # Doctrine entities
```

## 4.3 Routing Conventions

| Route Type | Prefix | Purpose |
|------------|--------|---------|
| REST API | `/api/v1/` | Main application endpoints |
| MCP | `/mcp/` | Model Context Protocol endpoints |
| Internal | - | Use UUIDs, avoid numeric IDs |

---

# 5. Code Style & Quality

## 5.1 Standards
- Every file: `declare(strict_types=1);`
- Use PHP 8.5 features: `readonly` classes, constructor promotion
- PSR-12 coding standard
- Explicit, descriptive naming (e.g., `RssFeedParser` not `FeedService`)

## 5.2 Test Naming (PHP)

> **Critical:** PHP CS Fixer enforces **camelCase** method names — no underscores.

Pattern: `test{Method}{Scenario}{Expected}`

```php
// Good
public function testInvokeWithValidUrlReturnsFeedId(): void
public function testInvokeWithInvalidCategoryThrowsNotFoundException(): void

// Bad — PHP CS Fixer will reject
public function testInvoke_ValidUrl_ReturnsFeedId(): void
```

## 5.3 Quality Tools

| Tool | Purpose | Command |
|------|---------|---------|
| PHP CS Fixer | Code style (PSR-12) | `make lint` |
| PHPStan | Static analysis (level 9) | `make analyse` |
| Rector | Code modernization | `make rector` |
| GrumPHP | Pre-commit gate | `make grumphp` |

---

# 6. Agent Instructions & Boundaries

## 6.0 Communication Style
When working on this project, the agent MUST:
- **Explain choices in real-time:** Before implementing, explain WHY a particular approach is chosen
- **Narrate actions:** Describe what you are doing as you do it
- **Justify technical decisions:** When choosing a pattern, library, or approach, explain the reasoning
- **Highlight trade-offs:** When multiple valid approaches exist, explain the pros/cons
- **Compare alternatives explicitly:** For non-trivial decisions, list at least two alternatives

## 6.1 ALWAYS DO
- Follow CQRS, Hexagonal Architecture, SOLID
- Validate inputs strictly via InputDTOs
- Run `make quality` on generated PHP code
- Write tests for every change (PHPUnit/Vitest)
- Use async processing for RSS crawling and AI inference
- Ensure AI summaries retain source URLs (factual integrity)
- Use Conventional Commits for git messages
- After implementing a feature that passes `make quality` and tests, update `docs/ROADMAP.md`

## 6.2 ASK FIRST
- Adding new composer/npm packages
- Changing PostgreSQL schema or pgvector dimensions
- Modifying MCP protocol implementation
- Changing LLM provider configurations

## 6.3 NEVER DO
- Commit to `master` directly — always create a `feat/` or `fix/` branch and PR
- Hardcode API keys (use environment variables)
- Perform blocking HTTP/AI calls in web request cycle
- Add coupling between domains
- Write business logic in controllers
- Create "god services"
- Store personal data without documented purpose (GDPR)
- Send raw user data to external AI services without anonymization (GDPR)

---

# 7. Exception Handling (RFC 7807)

All API errors follow **RFC 7807 - Problem Details for HTTP APIs**.

## 7.1 Problem Details Format
```json
{
  "type": "https://signalist.app/problems/feed-not-found",
  "title": "Feed Not Found",
  "status": 404,
  "detail": "The feed with ID 550e8400-e29b-41d4-a716-446655440000 was not found",
  "instance": "/api/v1/feeds/550e8400-e29b-41d4-a716-446655440000"
}
```

## 7.2 Problem Types

| Type URI | Title | Status | When |
|----------|-------|--------|------|
| `/problems/validation-error` | Validation Error | 400 | Input validation failed |
| `/problems/not-found` | Resource Not Found | 404 | Entity doesn't exist |
| `/problems/conflict` | Resource Conflict | 409 | Duplicate or conflict |
| `/problems/unprocessable` | Unprocessable Entity | 422 | Business rule violation |
| `/problems/quota-exceeded` | Quota Exceeded | 402 | Rate/usage limit hit |
| `/problems/internal-error` | Internal Error | 500 | Unexpected server error |

---

# 8. Testing

Testing is **mandatory**. Target 80%+ coverage on business logic (enforced in CI).

## 8.1 Backend (PHPUnit)

| Type | Purpose | Location |
|------|---------|----------|
| **Unit** | Test Handlers, Domain Models | `tests/Unit/` |
| **API** | Full HTTP flow via Behat | `features/api/` |

**Naming:** `test{Method}{Scenario}{Expected}` (camelCase, no underscores — enforced by PHP CS Fixer)

## 8.2 Frontend (Vitest + RTL)
- Test React components and hooks
- Mock API calls
- Initialize i18n in `src/test/setup.ts` (already done)
- Spotlight command parsing

---

# 9. API Response Formats

## 9.1 Single Resource
```json
{ "id": "uuid", "title": "Feed Title", "url": "https://..." }
```

## 9.2 Paginated Collection (`GET /api/v1/articles`)
```json
{
  "items": [...],
  "total": 100,
  "page": 1,
  "limit": 20,
  "pages": 5
}
```

## 9.3 Hydra Collection (API Platform resources)
```json
{
  "@context": "/api/v1/contexts/Feed",
  "@type": "Collection",
  "member": [...],
  "totalItems": 10
}
```

---

# 10. Project Specifics

## 10.1 Vector Search (pgvector)
Pipeline: `RSS Fetch → HTML Clean (Readability) → Chunking → Embedding → Postgres`

```sql
CREATE EXTENSION vector;
CREATE TABLE article_embeddings (
    id UUID PRIMARY KEY,
    article_id UUID REFERENCES articles(id),
    embedding vector(1536),
    chunk_index INT
);
CREATE INDEX ON article_embeddings
USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100);
```

## 10.2 MCP Server
```php
#[AsTool(name: 'search_articles', description: 'Search articles by semantic query')]
final class SearchArticlesTool
{
    public function __invoke(string $query, int $limit = 10): array { ... }
}
```

## 10.3 Spotlight Command Interface
- Activation: `Cmd+K`
- Shell: implemented in `frontend/src/components/Spotlight/`
- Natural Language Processing for intent mapping

---

# 11. Git Conventions

## 11.1 Branching Strategy
- Branch naming: `feat/<description>`, `fix/<description>`, `refactor/<description>`, `chore/<description>`
- Create PR via `gh pr create` — never commit directly to `master`

## 11.2 Commit Messages (Conventional Commits v1.0.0)

```
feat(feed): add RSS validation before save
fix(newsletter): correct word count calculation
refactor(spotlight): extract command parser
test(bookmark): add integration tests for tagging
chore(ci): bump actions to Node.js 24
```

**GrumPHP enforces 72-char line limit** on subject and body lines.

---

# 12. Slash Commands

| Command | Use For |
|---------|---------|
| `/new-feature <description>` | Start structured 9-step feature workflow |
| `/bug-fix <description>` | Start structured 8-step bug fix workflow |
| `/hotfix <description>` | Expedited minimal fix for production issues |
| `/review` | Code review of current git diff |
| `/quality` | Run `make quality` + unit tests and report |
| `/simplify` | Clean up recently changed code without changing behavior |

---

# 13. GDPR Compliance

All features involving personal data require privacy-by-design.

## 13.1 Core Principles

| Principle | Implementation |
|-----------|----------------|
| **Data Minimization** | Only collect data strictly necessary |
| **Purpose Limitation** | Data used only for stated purpose |
| **Storage Limitation** | Enforce retention periods |
| **Integrity & Confidentiality** | Encrypt at rest and in transit |

## 13.2 Agent GDPR Rules

### ALWAYS DO
- Add `deletedAt` soft-delete column to entities with personal data
- Anonymize data before sending to external AI services
- Include data in user export endpoints

### NEVER DO
- Store personal data without documented purpose
- Send raw personal data to AI providers
- Retain data beyond defined retention periods
- Log sensitive data (passwords, tokens) in plain text
