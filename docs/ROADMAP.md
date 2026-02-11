# Signalist Roadmap

> This document tracks every feature, sub-task, and deliverable across all project phases.
> **Status indicators:** `Done` | `In Progress` | `Not Started`
>
> Updated by the agent after each feature passes quality checks (`make quality`) and tests.

---

## Phase 1 — MVP: Core RSS Engine & Dashboard UI

### 1.1 Database Schema & Entities

| Task | Status |
|------|--------|
| `Feed` entity (url, title, status, lastFetchedAt, lastError) | Done |
| `Category` entity (name, slug, description, color, position) | Done |
| `Article` entity (guid, title, url, summary, content, author, imageUrl, publishedAt, isRead) | Done |
| `Bookmark` entity (notes, article relation, createdAt) | Done |
| `ArticleEmbedding` entity (embedding JSON, chunkIndex, chunkText) | Done |
| Initial migration (`Version20260131135830`) | Done |
| Unique constraints (feed.url, category.slug, bookmark.article_id) | Done |
| Indexes on `published_at`, `is_read` | Done |

### 1.2 Domain Layer (CQRS Handlers)

#### Feed Domain

| Task | Status |
|------|--------|
| `AddFeedCommand` + `AddFeedHandler` | Done |
| `UpdateFeedCommand` + `UpdateFeedHandler` | Done |
| `DeleteFeedCommand` + `DeleteFeedHandler` | Done |
| `GetFeedQuery` + `GetFeedHandler` | Done |
| `ListFeedsHandler` (no dedicated query object) | Done |
| `AddFeedInput` DTO | Done |
| `UpdateFeedInput` DTO | Done |
| `FeedRepositoryInterface` port | Done |
| `RssFetcherInterface` port | Done |
| `FeedNotFoundException` | Done |
| `FeedUrlAlreadyExistsException` | Done |
| Feed output DTO | Not Started |

#### Category Domain

| Task | Status |
|------|--------|
| `CreateCategoryCommand` + `CreateCategoryHandler` | Done |
| `UpdateCategoryCommand` + `UpdateCategoryHandler` | Done |
| `DeleteCategoryCommand` + `DeleteCategoryHandler` | Done |
| `GetCategoryQuery` + `GetCategoryHandler` | Done |
| `ListCategoriesQuery` + `ListCategoriesHandler` | Done |
| `CreateCategoryInput` DTO | Done |
| `UpdateCategoryInput` DTO | Done |
| `CategoryOutput` DTO | Done |
| `CategoryRepositoryInterface` port | Done |
| `CategoryNotFoundException` | Done |
| `CategoryHasFeedsException` | Done |
| `CategorySlugAlreadyExistsException` | Done |

#### Article Domain

| Task | Status |
|------|--------|
| `MarkArticleReadCommand` + `MarkArticleReadHandler` | Done |
| `GetArticleQuery` + `GetArticleHandler` | Done |
| `ListArticlesQuery` + `ListArticlesHandler` | Done |
| `ArticleRepositoryInterface` port | Done |
| `ArticleNotFoundException` | Done |
| Article input DTOs | Not Started |
| Article output DTO | Not Started |

#### Bookmark Domain

| Task | Status |
|------|--------|
| `CreateBookmarkCommand` + `CreateBookmarkHandler` | Done |
| `DeleteBookmarkCommand` + `DeleteBookmarkHandler` | Done |
| `GetBookmarkQuery` + `GetBookmarkHandler` | Done |
| `ListBookmarksHandler` (no dedicated query object) | Done |
| `CreateBookmarkInput` DTO | Done |
| `BookmarkRepositoryInterface` port | Done |
| `BookmarkNotFoundException` | Done |
| `ArticleAlreadyBookmarkedException` | Done |
| `ListBookmarksQuery` | Not Started |
| Bookmark output DTO | Not Started |

### 1.3 Infrastructure Layer

| Task | Status |
|------|--------|
| `DoctrineFeedRepository` | Done |
| `DoctrineCategoryRepository` | Done |
| `DoctrineArticleRepository` | Done |
| `DoctrineBookmarkRepository` | Done |
| `LaminasFeedRssFetcher` (RSS parser) | Done |
| `RssFetchResult` + `RssFetchedArticle` DTOs | Done |
| `ProblemException` base class (RFC 7807) | Done |
| `ConflictException` | Done |
| `NotFoundException` | Done |
| `ValidationException` | Done |
| `ProblemDetailsExceptionListener` | Done |

### 1.4 API Platform Integration

| Task | Status |
|------|--------|
| `CategoryResource` (API Platform resource) | Done |
| `FeedResource` (API Platform resource) | Done |
| `ArticleResource` (API Platform resource) | Done |
| `BookmarkResource` (API Platform resource) | Done |
| `CategoryStateProcessor` + `CategoryStateProvider` | Done |
| `FeedStateProcessor` + `FeedStateProvider` | Done |
| `ArticleStateProcessor` + `ArticleStateProvider` | Done |
| `BookmarkStateProcessor` + `BookmarkStateProvider` | Done |

### 1.5 Async Processing

| Task | Status |
|------|--------|
| `CrawlFeedMessage` | Done |
| `CrawlFeedMessageHandler` | Done |
| Symfony Messenger + Redis transport config | Done |

### 1.6 Frontend (React + TypeScript + MUI)

| Task | Status |
|------|--------|
| Vite + React 19 + TypeScript setup | Done |
| MUI theme configuration | Done |
| React Router (3 routes) | Done |
| Axios API client with proxy | Done |
| `AppLayout` (shell: header + sidebar) | Done |
| `Header` component | Done |
| `Sidebar` component | Done |
| `Dashboard` page | Done |
| `CategoryPage` | Done |
| `BookmarksPage` | Done |
| `ArticleList` + `ArticleCard` components | Done |
| `CategoryDialog` (create/edit) | Done |
| `AddFeedDialog` | Done |
| `BookmarkList` component | Done |
| `EmptyState`, `ErrorAlert`, `LoadingSpinner` | Done |
| `useArticles` hook | Done |
| `useCategories` hook | Done |
| `useFeeds` hook | Done |
| `useBookmarks` hook | Done |
| API modules (articles, categories, feeds, bookmarks) | Done |
| TypeScript types for API models | Done |
| Full-text search UI | Not Started |
| Article detail view / reader | Not Started |
| Feed management page (edit, pause, delete) | Not Started |
| Responsive design polish | Not Started |

### 1.7 DevOps & Tooling

| Task | Status |
|------|--------|
| Docker Compose (FrankenPHP + PostgreSQL 16 + Redis 7) | Done |
| FrankenPHP Dockerfile (worker mode) | Done |
| PostgreSQL init script (pgvector extension) | Done |
| Makefile (50+ commands) | Done |
| PHP CS Fixer configuration | Done |
| PHPStan configuration | Done |
| Rector configuration | Done |
| GrumPHP pre-commit hooks | Done |
| `.env` / `.env.test` / `.env.local.example` | Done |

### 1.8 Testing (Phase 1)

| Task | Status |
|------|--------|
| PHPUnit configuration (`phpunit.xml.dist`) | Done |
| `AddFeedHandlerTest` | Done |
| `DeleteFeedHandlerTest` | Done |
| `CrawlFeedMessageHandlerTest` | Done |
| `ListArticlesHandlerTest` | Done |
| `MarkArticleReadHandlerTest` | Done |
| `CreateBookmarkHandlerTest` | Done |
| `DeleteBookmarkHandlerTest` | Done |
| `UpdateFeedHandlerTest` | Not Started |
| `GetFeedHandlerTest` | Not Started |
| `ListFeedsHandlerTest` | Not Started |
| `CreateCategoryHandlerTest` | Not Started |
| `UpdateCategoryHandlerTest` | Not Started |
| `DeleteCategoryHandlerTest` | Not Started |
| `GetCategoryHandlerTest` | Not Started |
| `ListCategoriesHandlerTest` | Not Started |
| `GetArticleHandlerTest` | Not Started |
| `GetBookmarkHandlerTest` | Not Started |
| `ListBookmarksHandlerTest` | Not Started |
| Integration tests (Doctrine repositories) | Not Started |
| Behat feature files (API scenarios) | Not Started |
| Frontend component tests (Vitest) | Not Started |

### 1.9 Data Fixtures

| Task | Status |
|------|--------|
| `CategoryFixture` | Done |
| `FeedFixture` | Done |
| `ArticleFixture` | Done |
| `BookmarkFixture` | Done |
| Seed data population (realistic content) | Not Started |

---

## Phase 2 — AI Layer: Summaries, Embeddings & Semantic Search

### 2.1 Symfony AI Integration

| Task | Status |
|------|--------|
| Install and configure `symfony/ai` | Not Started |
| Configure LLM providers (OpenAI, Anthropic, Mistral) | Not Started |
| Environment variables for API keys | Not Started |
| AI client adapter (`Infrastructure/AI/`) | Not Started |

### 2.2 Article Embeddings (pgvector)

| Task | Status |
|------|--------|
| Enable pgvector `vector(1536)` column type (replace JSON) | Not Started |
| IVFFlat index on embeddings | Not Started |
| HTML cleaning pipeline (Readability) | Not Started |
| Text chunking strategy | Not Started |
| `GenerateEmbeddingMessage` (async) | Not Started |
| `GenerateEmbeddingMessageHandler` | Not Started |
| Embedding generation adapter (OpenAI / Mistral) | Not Started |
| Unit tests for embedding pipeline | Not Started |

### 2.3 Semantic Search

| Task | Status |
|------|--------|
| Search domain (`src/Domain/Search/`) | Not Started |
| `SemanticSearchQuery` + `SemanticSearchHandler` | Not Started |
| `SearchRepositoryInterface` port | Not Started |
| `PgvectorSearchRepository` adapter | Not Started |
| Cosine similarity search endpoint | Not Started |
| Combined full-text + semantic search | Not Started |
| Search API Platform resource | Not Started |
| Frontend search UI with results | Not Started |
| Unit + integration tests | Not Started |

### 2.4 LLM Summarization

| Task | Status |
|------|--------|
| `SummarizeArticleMessage` (async) | Not Started |
| `SummarizeArticleMessageHandler` | Not Started |
| Summarization prompt templates | Not Started |
| Source URL retention in summaries | Not Started |
| Summary storage (Article entity field) | Not Started |
| Frontend summary display | Not Started |
| Unit tests for summarization | Not Started |

### 2.5 Auto-Tagging

| Task | Status |
|------|--------|
| Tag entity + migration | Not Started |
| `AutoTagArticleMessage` (async) | Not Started |
| `AutoTagArticleMessageHandler` | Not Started |
| LLM-based tag extraction | Not Started |
| Tag CRUD domain (Command/Query/Handler) | Not Started |
| Tag API endpoint | Not Started |
| Frontend tag display + filtering | Not Started |
| Unit tests | Not Started |

### 2.6 Data Anonymization (GDPR)

| Task | Status |
|------|--------|
| `ArticleAnonymizer` service | Not Started |
| PII detection (email, phone, names, IPs) | Not Started |
| Anonymization before LLM calls | Not Started |
| Logging of data sent to external AI services | Not Started |
| Unit tests for anonymizer | Not Started |

---

## Phase 3 — Automation: Newsletters, Scheduling & Sync

### 3.1 Newsletter Domain

| Task | Status |
|------|--------|
| Newsletter entity + migration | Not Started |
| `CreateNewsletterCommand` + `CreateNewsletterHandler` | Not Started |
| `UpdateNewsletterCommand` + `UpdateNewsletterHandler` | Not Started |
| `DeleteNewsletterCommand` + `DeleteNewsletterHandler` | Not Started |
| `GetNewsletterQuery` + `GetNewsletterHandler` | Not Started |
| `ListNewslettersQuery` + `ListNewslettersHandler` | Not Started |
| Newsletter input/output DTOs | Not Started |
| `NewsletterRepositoryInterface` port | Not Started |
| `DoctrineNewsletterRepository` adapter | Not Started |
| Newsletter API Platform resource | Not Started |
| Unit tests for all handlers | Not Started |

### 3.2 Newsletter Content Generation

| Task | Status |
|------|--------|
| `NewsletterContentBuilder` service | Not Started |
| Reading time calculation (200 wpm) | Not Started |
| Category-grouped article selection | Not Started |
| LLM-generated concise summaries | Not Started |
| Clickable titles with source URLs | Not Started |
| HTML email template | Not Started |
| Unit tests | Not Started |

### 3.3 Newsletter Scheduling

| Task | Status |
|------|--------|
| Symfony Scheduler integration | Not Started |
| Daily / weekly / custom schedule options | Not Started |
| `GenerateNewsletterMessage` (async) | Not Started |
| `GenerateNewsletterMessageHandler` | Not Started |
| Symfony Mailer configuration | Not Started |
| Email delivery adapter | Not Started |
| Schedule management UI (frontend) | Not Started |
| Unit + integration tests | Not Started |

### 3.4 Raindrop.io Sync

| Task | Status |
|------|--------|
| OAuth2 flow for Raindrop.io | Not Started |
| `RaindropSyncMessage` (async) | Not Started |
| `RaindropSyncMessageHandler` | Not Started |
| Raindrop API client (`Infrastructure/External/Raindrop/`) | Not Started |
| Bidirectional bookmark sync | Not Started |
| Conflict resolution strategy | Not Started |
| Sync status UI (frontend) | Not Started |
| Unit tests | Not Started |

---

## Phase 4 — Ecosystem: Spotlight, MCP Server & Social Sharing

### 4.1 Spotlight Command Interface

| Task | Status |
|------|--------|
| Spotlight domain (`src/Domain/Spotlight/`) | Not Started |
| `Cmd+K` activation (frontend) | Not Started |
| Command parser (NLP intent mapping) | Not Started |
| CRUD intent handlers (add feed, create category, etc.) | Not Started |
| AI query handler (ask questions about articles) | Not Started |
| Spotlight UI component (overlay, suggestions, results) | Not Started |
| Frontend + backend integration | Not Started |
| Unit tests for command parser | Not Started |

### 4.2 MCP Server (Model Context Protocol)

| Task | Status |
|------|--------|
| MCP infrastructure (`src/Infrastructure/MCP/`) | Not Started |
| `#[AsTool]` tool definitions | Not Started |
| `search_articles` tool | Not Started |
| `get_feed_summary` tool | Not Started |
| `list_categories` tool | Not Started |
| `get_article` tool | Not Started |
| MCP routing (`/mcp/` prefix) | Not Started |
| Authentication for MCP endpoints | Not Started |
| Integration tests | Not Started |

### 4.3 Social Sharing

| Task | Status |
|------|--------|
| Share intent model | Not Started |
| WhatsApp share adapter | Not Started |
| X (Twitter) share adapter | Not Started |
| LinkedIn share adapter | Not Started |
| Threads share adapter | Not Started |
| Bluesky share adapter | Not Started |
| Share button UI (frontend) | Not Started |
| Unit tests | Not Started |

---

## Cross-Cutting: GDPR Compliance

| Task | Status |
|------|--------|
| `UserConsent` entity + migration | Not Started |
| Consent management endpoints (grant, withdraw) | Not Started |
| `GET /api/v1/user/data-export` (Access right) | Not Started |
| `DELETE /api/v1/user/account` (Erasure right) | Not Started |
| `GET /api/v1/user/data-export?format=portable` (Portability) | Not Started |
| `PUT /api/v1/user/profile` (Rectification) | Not Started |
| `POST /api/v1/user/restrict-processing` (Restriction) | Not Started |
| `POST /api/v1/user/opt-out` (Objection) | Not Started |
| Soft-delete (`deletedAt`) on personal data entities | Not Started |
| Cascade deletion for related personal data | Not Started |
| Data retention scheduler (90-day articles, 30-day sessions) | Not Started |
| Encrypted sensitive columns (`EncryptedStringType`) | Not Started |
| Data access audit logging (`DataAccessAuditor`) | Not Started |
| Privacy policy documentation | Not Started |
| DPA documentation for AI providers | Not Started |
| GDPR-specific problem types (consent-required, etc.) | Not Started |

---

## Cross-Cutting: Documentation & Quality

| Task | Status |
|------|--------|
| `CLAUDE.md` (project guide) | Done |
| `README.md` | Done |
| `docs/MARKETING-STRATEGY.md` | Done |
| `docs/TESTING-GUIDE.md` | Done |
| `docs/ROADMAP.md` (this file) | Done |
| AI agent definitions (`.ai-agents/`) | Done |
| API documentation (OpenAPI / Swagger) | Not Started |
| Architecture decision records (ADRs) | Not Started |
