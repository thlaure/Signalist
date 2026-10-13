# Signalist Roadmap

> This document tracks every feature, sub-task, and deliverable across all project phases.
> **Status indicators:** `Done` | `In Progress` | `Not Started` | `Deferred`
>
> Updated by the agent after each feature passes quality checks (`make quality`) and tests.


> Local implementation covers privacy, pgvector storage, provider-independent AI pipelines, search/Q&A, tags, newsletters, teams, billing, OAuth/sync, social drafts/publishing, Spotlight and read-only MCP. Live AI/provider setup, production domain/deployment and operator agreements remain external prerequisites. See [deployment guide](DEPLOYMENT.md).
> Task names below describe capabilities: handlers/messages are consolidated where useful, new async endpoints use thin controllers, and Doctrine supplies persistence without redundant repository wrappers. An implementation marked Done does not claim successful live provider validation.
> Verification on 2026-10-13: 193 backend tests, 85 API scenarios, 42 frontend tests and five offline Bluesky SDK tests passed; domain statement coverage is 82.65%. Frontend/landing builds and local quality checks passed. See [review notes](PR-NOTES.md) for exact checks, migration evidence and remaining launch risks. [Browser QA](BROWSER-QA.md) records the local end-to-end pass and its limits.

---

## Phase 1 — MVP: Core RSS Engine & Dashboard UI

### 1.1 Database Schema & Entities

| Task | Status |
|------|--------|
| `Feed` entity (url, title, status, lastFetchedAt, lastError) | Done |
| `Category` entity (name, slug, description, color, position) | Done |
| `Article` entity (guid, title, url, summary, content, author, imageUrl, publishedAt, isRead) | Done |
| `Bookmark` entity (notes, article relation, createdAt) | Done |
| `ArticleEmbedding` entity (vector(1536), chunkIndex, chunkText) | Done |
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
| Feed output DTO | Done |

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
| Article input DTOs | Done (N/A — articles are created via RSS crawl, not user input) |
| Article output DTO | Done |

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
| `ListBookmarksQuery` | Done |
| Bookmark output DTO | Done |

#### Auth Domain

| Task | Status |
|------|--------|
| `User` entity (email, password, roles, deletedAt, emailVerifiedAt) | Done |
| `LoginInput` DTO (email + password validation) | Done |
| `LoginHandler` (JWT token generation) | Done |
| `LoginController` (`POST /api/v1/auth/login`) | Done |
| `RegisterInput` DTO + `RegisterCommand` + `RegisterHandler` | Done |
| `RegisterController` (`POST /api/v1/auth/register`) | Done |
| `EmailVerificationTokenGeneratorInterface` port | Done |
| `HmacEmailVerificationTokenGenerator` adapter (HMAC-SHA256, 24h TTL) | Done |
| `SendVerificationEmailMessage` + async handler | Done |
| `VerifyEmailInput` DTO + `VerifyEmailCommand` + `VerifyEmailHandler` | Done |
| `VerifyEmailController` (`POST /api/v1/auth/verify-email`) | Done |
| `ResendVerificationInput` DTO + `ResendVerificationCommand` + `ResendVerificationHandler` | Done |
| `ResendVerificationController` (`POST /api/v1/auth/resend-verification`) | Done |
| `EmailNotVerifiedException` (403) | Done |
| `InvalidVerificationTokenException` (400) | Done |
| `InvalidCredentialsException` | Done |
| `EmailAlreadyExistsException` | Done |
| `DeletedAccountException` | Done |
| LexikJWTAuthenticationBundle configuration | Done |
| JWT firewall for `/api/v1/*` endpoints | Done |
| Symfony Mailer + Mailpit (local dev) | Done |

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
| User-scoped data filtering (owner relationship on all entities) | Done |
| Handle serializer `MissingConstructorArgumentsException` as 422 | Done |

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
| React Router (7 routes) | Done |
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
| `LoginPage` | Done |
| `RegisterPage` | Done |
| `CheckEmailPage` (post-registration) | Done |
| `VerifyEmailPage` (email verification) | Done |
| `ProtectedRoute` component | Done |
| Auth API functions (login, register, verifyEmail, resendVerification) | Done |
| `AuthContext` + `useAuth` hook | Done |
| `EmptyState`, `ErrorAlert`, `LoadingSpinner` | Done |
| `useArticles` hook | Done |
| `useCategories` hook | Done |
| `useFeeds` hook | Done |
| `useBookmarks` hook | Done |
| API modules (articles, categories, feeds, bookmarks) | Done |
| TypeScript types for API models | Done |
| Full-text search UI | Done |
| Article detail view / reader | Done |
| Feed management page (edit, pause, delete) | Done |
| Responsive design polish | Done |

### 1.7 DevOps & Tooling

| Task | Status |
|------|--------|
| Docker Compose (FrankenPHP + PostgreSQL 16 + Redis 7 + Mailpit) | Done |
| FrankenPHP Dockerfile (worker mode) | Done |
| PostgreSQL init script (pgvector extension) | Done |
| Makefile (50+ commands) | Done |
| PHP CS Fixer configuration | Done |
| PHPStan configuration | Done |
| Rector configuration | Done |
| GrumPHP pre-commit hooks | Done |
| Frontend pre-commit hooks (husky + lint-staged) | Done |
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
| `UpdateFeedHandlerTest` | Done |
| `GetFeedHandlerTest` | Done |
| `ListFeedsHandlerTest` | Done |
| `CreateCategoryHandlerTest` | Done |
| `UpdateCategoryHandlerTest` | Done |
| `DeleteCategoryHandlerTest` | Done |
| `GetCategoryHandlerTest` | Done |
| `ListCategoriesHandlerTest` | Done |
| `GetArticleHandlerTest` | Done |
| `GetBookmarkHandlerTest` | Done |
| `ListBookmarksHandlerTest` | Done |
| `LoginHandlerTest` | Done |
| `LoginInputTest` (DTO validation) | Done |
| `RegisterHandlerTest` | Done |
| `VerifyEmailHandlerTest` | Done |
| `ResendVerificationHandlerTest` | Done |
| `HmacEmailVerificationTokenGeneratorTest` | Done |
| Behat: categories.feature | Done |
| Behat: feeds.feature | Done |
| Behat: articles.feature | Done |
| Behat: bookmarks.feature | Done |
| Behat: login.feature (8 scenarios) | Done |
| Behat: register.feature | Done |
| Behat: verify_email.feature (4 scenarios) | Done |
| Behat JWT auth support in ApiContext | Done |
| PHPUnit coverage reporting (93%+ on Domain) | Done |
| CI coverage threshold enforcement (80%) | Done |
| `FeedOutputTest` | Done |
| `ArticleOutputTest` | Done |
| `BookmarkOutputTest` | Done |
| GrumPHP: phpunit + behat + rector tasks | Done |
| Integration tests (Doctrine repositories) | Done |
| Frontend component tests (Vitest) | Done |

### 1.9 Data Fixtures

| Task | Status |
|------|--------|
| `CategoryFixture` | Done |
| `FeedFixture` | Done |
| `ArticleFixture` | Done |
| `BookmarkFixture` | Done |
| `UserFixture` | Done |
| Seed data population (realistic content) | Done |

---

## Phase 2 — Landing Page

### 2.1 Design & Content

| Task | Status |
|------|--------|
| Value proposition copywriting | Done |
| Wireframes / mockup (Figma or equivalent) | Done |
| Pricing tiers definition (free, team, enterprise) | Done (quotas; commercial prices pending) |

### 2.2 Implementation

| Task | Status |
|------|--------|
| Landing page setup (`landing/` — Astro 7 + Tailwind v4) | Done |
| Hero section (headline, subheadline, CTA, inbox preview) | Done |
| Features section (Monitor / Curate / Synthesize / Publish) | Done |
| Pricing section (Free / Team / Enterprise) | Done |
| Product principles section (fabricated testimonials removed) | Done |
| Waitlist / early access form with confirmed, withdrawable backend | Done |
| SEO meta tags + Open Graph | Done |
| Responsive design (mobile + desktop) | Done |

### 2.3 Infrastructure

| Task | Status |
|------|--------|
| Domain configuration | Not Started (external setup) |
| Deploy to production (Vercel / Netlify / other) | Not Started (external setup) |
| Analytics (privacy-friendly, e.g. Plausible) | Done |

---

## Phase 3 — AI Layer: Summaries, Embeddings & Semantic Search

### 3.1 Symfony AI Integration

| Task | Status |
|------|--------|
| Install and configure `symfony/ai` | Deferred (provider setup) |
| Configure LLM providers (OpenAI, Anthropic, Mistral) | Deferred (provider setup) |
| Environment variables for API keys | Deferred (provider setup) |
| AI client adapter (`Infrastructure/AI/`) | Done (provider ports; live adapter deferred) |

### 3.2 Article Embeddings (pgvector)

| Task | Status |
|------|--------|
| Enable pgvector `vector(1536)` column type (replace JSON) | Done |
| IVFFlat index on embeddings | Done |
| HTML cleaning pipeline (Readability) | Done |
| Text chunking strategy | Done |
| `GenerateEmbeddingMessage` (async) | Done |
| `GenerateEmbeddingMessageHandler` | Done |
| Embedding generation adapter (OpenAI / Mistral) | Deferred (provider setup) |
| Unit tests for embedding pipeline | Done |

### 3.3 Semantic Search

| Task | Status |
|------|--------|
| Search domain (`src/Domain/Search/`) | Done |
| `SemanticSearchQuery` + `SemanticSearchHandler` | Done |
| `SearchRepositoryInterface` port | Done |
| `PgvectorSearchRepository` adapter | Done |
| Cosine similarity search endpoint | Done |
| Combined full-text + semantic search | Done |
| Search API Platform resource | Done |
| Frontend search UI with results | Done |
| Unit + integration tests | Done |

### 3.4 LLM Summarization

| Task | Status |
|------|--------|
| `SummarizeArticleMessage` (async) | Done |
| `SummarizeArticleMessageHandler` | Done |
| Summarization prompt templates | Deferred (provider setup) |
| Source URL retention in summaries | Done |
| Summary storage (Article entity field) | Done |
| Frontend summary display | Done |
| Unit tests for summarization | Done |

### 3.5 Auto-Tagging

| Task | Status |
|------|--------|
| Tag entity + migration | Done |
| `AutoTagArticleMessage` (async) | Done |
| `AutoTagArticleMessageHandler` | Done |
| LLM-based tag extraction | Deferred (provider setup) |
| Tag CRUD domain (Command/Query/Handler) | Done |
| Tag API endpoint | Done |
| Frontend tag display + filtering | Done |
| Unit tests | Done |

### 3.6 Data Anonymization (GDPR)

| Task | Status |
|------|--------|
| `ArticleAnonymizer` service | Done |
| PII detection (email, phone, names, IPs) | In Progress (common identifiers and known names; arbitrary-name NER pending) |
| Anonymization before LLM calls | Done |
| Logging of data sent to external AI services | Done |
| Unit tests for anonymizer | Done |

---

## Phase 4 — Automation: Newsletters, Scheduling & Sync

### 4.1 Newsletter Domain

| Task | Status |
|------|--------|
| Newsletter entity + migration | Done |
| `CreateNewsletterCommand` + `CreateNewsletterHandler` | Done |
| `UpdateNewsletterCommand` + `UpdateNewsletterHandler` | Done |
| `DeleteNewsletterCommand` + `DeleteNewsletterHandler` | Done |
| `GetNewsletterQuery` + `GetNewsletterHandler` | Done |
| `ListNewslettersQuery` + `ListNewslettersHandler` | Done |
| Newsletter input/output DTOs | Done |
| `NewsletterRepositoryInterface` port | Done |
| `DoctrineNewsletterRepository` adapter | Done |
| Newsletter API Platform resource | Done |
| Unit tests for all handlers | Done |

### 4.2 Newsletter Content Generation

| Task | Status |
|------|--------|
| `NewsletterContentBuilder` service | Done |
| Reading time calculation (200 wpm) | Done |
| Category-grouped article selection | Done |
| LLM-generated concise summaries | Deferred (uses stored AI summaries when available) |
| Clickable titles with source URLs | Done |
| HTML email template | Done |
| Unit tests | Done |

### 4.3 Newsletter Scheduling

| Task | Status |
|------|--------|
| Symfony Scheduler integration | Done |
| Daily / weekly / custom schedule options | Done |
| `GenerateNewsletterMessage` (async) | Done |
| `GenerateNewsletterMessageHandler` | Done |
| Symfony Mailer configuration | Done |
| Email delivery adapter | Done |
| Schedule management UI (frontend) | Done |
| Unit + integration tests | Done |

### 4.4 Raindrop.io Sync

| Task | Status |
|------|--------|
| OAuth2 flow for Raindrop.io | In Progress (secure state binding implemented; provider state echo unverified) |
| `RaindropSyncMessage` (async) | Done |
| `RaindropSyncMessageHandler` | Done |
| Raindrop API client (`Infrastructure/External/Raindrop/`) | Done |
| Bidirectional bookmark sync | Done |
| Conflict resolution strategy | Done |
| Sync status UI (frontend) | Done |
| Unit tests | Done |

### 4.5 Social Recap (AI-Generated Weekly Post)

> Reuses Phase 4.2 content aggregation + Phase 3 LLM summarization to generate
> platform-native social posts from the week's curated articles.

| Task | Status |
|------|--------|
| `SocialRecap` entity + migration (platform, period, content, status) | Done |
| `CreateSocialRecapCommand` + `CreateSocialRecapHandler` | Done |
| `DeleteSocialRecapCommand` + `DeleteSocialRecapHandler` | Done |
| `GetSocialRecapQuery` + `GetSocialRecapHandler` | Done |
| `ListSocialRecapsQuery` + `ListSocialRecapsHandler` | Done |
| `SocialRecapRepositoryInterface` port | Done |
| `DoctrineSocialRecapRepository` adapter | Done |
| `SocialRecapContentBuilder` service (reuses `NewsletterContentBuilder` aggregation) | Done |
| Platform output formatters: LinkedIn, X (thread), Instagram caption | Done |
| LLM prompt templates per platform (tone, length, hashtag style) | Done |
| `GenerateSocialRecapMessage` (async) + handler | Done |
| Social Recap API Platform resource | Done |
| Frontend: recap generator UI (select period, platform, preview, copy) | Done |
| Unit tests for content builder + formatters | Done |

---

## Phase 5 — Team: Multi-User Workspace & Collaborative Curation

### 5.1 Organization & Roles

| Task | Status |
|------|--------|
| `Organization` entity + migration | Done |
| `OrganizationMember` entity (userId, orgId, role) + migration | Done |
| Roles: `admin`, `curator`, `reader` | Done |
| `InviteMemberCommand` + `InviteMemberHandler` | Done |
| `RemoveMemberCommand` + `RemoveMemberHandler` | Done |
| `UpdateMemberRoleCommand` + `UpdateMemberRoleHandler` | Done |
| Organization API endpoints | Done |
| Role-based access control (Symfony voters) | Done |
| Unit tests | Done |

### 5.2 Shared Workspace

| Task | Status |
|------|--------|
| Feeds and categories scoped to organization | Done |
| Shared bookmarks with curator attribution | Done |
| Collaborative annotations on bookmarks | Done |
| Activity feed (who curated what, when) | Done |
| Per-member read status (independent of shared articles) | Done |
| Frontend team workspace UI | Done |
| Unit + integration tests | Done |

### 5.3 Billing & Plans

| Task | Status |
|------|--------|
| Plan model (free, team, enterprise) | Done |
| Per-seat pricing logic | Done |
| Usage limits per plan (feeds, members, AI calls) | Done |
| Stripe integration (subscriptions) | Done |
| Plan management UI | Done |

---

## Phase 6 — Ecosystem: Spotlight, MCP Server & Social Sharing

### 6.1 Spotlight Command Interface

| Task | Status |
|------|--------|
| Spotlight domain (`src/Domain/Spotlight/`) | Done |
| `Cmd+K` activation (frontend) | Done |
| Command parser (NLP intent mapping) | Done (bounded English/French grammar; AI intent extraction deferred) |
| CRUD intent handlers (add feed, create category, etc.) | Done |
| AI query handler (ask questions about articles) | Done |
| Spotlight UI component (overlay, suggestions, results) | Done |
| Frontend + backend integration | Done |
| Unit tests for command parser | Done |

### 6.2 MCP Server (Model Context Protocol)

| Task | Status |
|------|--------|
| MCP infrastructure (`src/Infrastructure/MCP/`) | Done |
| SDK tool definitions (explicit registration) | Done |
| `search_articles` tool | Done |
| `get_feed_summary` tool | Done |
| `list_categories` tool | Done |
| `get_article` tool | Done |
| MCP routing (`/mcp/` prefix) | Done |
| Authentication for MCP endpoints | Done |
| Integration tests | Done |

### 6.3 Social Sharing

#### 6.3.1 Share Links (link-based, no OAuth required)

| Task | Status |
|------|--------|
| Share intent model | Done |
| WhatsApp share adapter | Done |
| X (Twitter) share adapter | Done |
| LinkedIn share adapter | Done |
| Threads share adapter | Done |
| Bluesky share adapter | Done |
| Share button UI (frontend) | Done |
| Unit tests | Done |

#### 6.3.2 Crossposting via Platform APIs (requires OAuth2 per platform)

> Upgrade path from 6.3.1 — posts content directly via platform APIs.
> Builds on OAuth2 infrastructure established in Phase 4.4 (Raindrop.io).

| Task | Status |
|------|--------|
| `SocialAccount` entity + migration (platform, accessToken, refreshToken, expiresAt) | Done |
| `SocialAccountRepositoryInterface` port | Done |
| `DoctrineSocialAccountRepository` adapter | Done |
| OAuth2 flow: LinkedIn (`Infrastructure/External/LinkedIn/`) | Done |
| OAuth2 flow: X / Twitter (`Infrastructure/External/X/`) | Done |
| OAuth2 flow: Bluesky (`Infrastructure/External/Bluesky/`) | Done |
| `CrosspostMessage` (async) + `CrosspostMessageHandler` | Done |
| `CrosspostAdapterInterface` port (per-platform implementations) | Done |
| Token refresh strategy (background job) | Done |
| Crosspost status tracking (pending, published, failed) | Done |
| Frontend: connect social accounts UI (OAuth2 redirect flow) | Done |
| Frontend: crosspost action on articles + recap posts | Done |
| Unit tests for adapters | Done |

---

## Cross-Cutting: GDPR Compliance

| Task | Status |
|------|--------|
| `UserConsent` entity + migration | Done |
| Consent management endpoints (grant, withdraw) | Done |
| `GET /api/v1/user/data-export` (Access right) | Done |
| `DELETE /api/v1/user/account` (Erasure right) | Done |
| `GET /api/v1/user/data-export?format=portable` (Portability) | Done |
| `PUT /api/v1/user/profile` (Rectification) | Done |
| `POST /api/v1/user/restrict-processing` (Restriction) | Done |
| `POST /api/v1/user/opt-out` (Objection) | Done |
| User tombstone (`deletedAt`) with immediate owned personal-data erasure | Done |
| Cascade deletion for related personal data | Done |
| Data retention scheduler (90-day articles, 30-day sessions) | Done |
| Encrypted credential columns (sodium and SDK ciphertext) | Done |
| Data access audit logging (`DataAccessAuditor`) | Done |
| Privacy policy documentation | In Progress (deployment drafts; operator completion required) |
| DPA documentation for AI providers | In Progress (deployment drafts; operator completion required) |
| GDPR-specific problem types (consent-required, etc.) | Done |

---

## Cross-Cutting: Documentation & Quality

| Task | Status |
|------|--------|
| `CLAUDE.md` (project guide) | Done |
| `README.md` | Done |
| `docs/ROADMAP.md` (this file) | Done |
| `.claude/` (slash commands + workflows) | Done |
| API documentation (OpenAPI / Swagger) | Done |
