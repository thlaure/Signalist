# Remaining roadmap implementation plan

This working plan covers `docs/ROADMAP.md`. The user authorized autonomous local implementation and requested provider-independent work while deferring OpenAI setup. See `DEPLOYMENT.md` for implemented configuration and external prerequisites. This is not a production launch report.

## Inspected starting point

- RSS, categories, articles, bookmarks, authentication, and email verification exist.
- The landing page exists in English and French, but its waitlist has no backend.
- AI, newsletters, organizations, subscriptions, third-party sync, and MCP implementations are absent.
- Article embeddings currently use JSON, not a PostgreSQL vector column.
- User deletion timestamps exist; the complete privacy lifecycle does not.
- Existing dependencies are installed locally. No Compose services were running during inspection.

## Delivery order and acceptance

| Stage | Implementation | Acceptance evidence |
| --- | --- | --- |
| 1. Baseline | Start existing development services; verify current backend, frontend, and landing build | Existing quality and test gates pass, or failures documented before new work |
| 2. Privacy and launch foundation | Consent lifecycle, export, profile updates, processing restrictions, deletion, retention, access audit, encrypted external credentials; persisted waitlist with consent and withdrawal | Owner isolation, export contents, deletion cascades, restricted processing, retention, and waitlist validation tested |
| 3. AI intelligence | Symfony AI provider adapters, versioned prompts, HTML cleaning, text chunking, anonymization, async summaries/embeddings/tags, combined semantic and full-text search | Unit tests with deterministic provider doubles; database search integration tests; API and frontend interaction tests |
| 4. Content automation | Newsletter CRUD, reading-time budget, category selection, linked summaries, HTML preview, schedules, async delivery, social recap previews | Selection and reading-time tests; scheduled-job idempotency; authorization and rendering tests; mail delivery checked locally |
| 5. Teams and subscriptions | Organizations, invitations, roles, shared curation, annotations, activity, per-member read state, plan quotas, Stripe checkout/portal and verified webhooks | Cross-organization denial tests, role matrix tests, quota enforcement, webhook replay and signature tests |
| 6. Integrations and ecosystem | Raindrop OAuth and bidirectional sync, conflict policy, social share links, LinkedIn/X/Bluesky OAuth and async publishing, Cmd+K, authenticated read-only MCP tools | OAuth state and token handling tests, sync conflict tests, duplicate publishing prevention, scoped MCP responses, frontend command tests |
| 7. Release verification | Deployment configuration and runbook, privacy-friendly analytics configuration, honest landing copy, roadmap status updates | Full project checks, frontend and landing production builds, documented live integration prerequisites |

Each stage includes backend implementation, API contracts, translatable frontend flows, relevant tests, and documentation. Roadmap tasks become Done only after their applicable checks pass. External integrations retain an explicit live-verification status.

## Implementation scope authorized by the user

### Dependencies

Add only packages needed for roadmap features: Symfony AI bundle and provider bridges, Symfony Scheduler, an HTML readability parser, Stripe's PHP SDK, and an MCP server implementation compatible with the existing Symfony application. Exact package names, versions, and compatibility must be verified against official documentation before installation. Reuse existing HTTP, mail, React Query, router, and UI dependencies where possible.

### Database migrations

- Replace embedding JSON storage with pgvector `vector(1536)` and the planned IVFFlat index. Validate existing values before conversion; do not silently discard incompatible values.
- Add tags and article associations; waitlist entries; consent records and access audit metadata.
- Add newsletters, schedules, delivery state, social recaps, and synchronization state.
- Add organizations, memberships, invitations, annotations, activity, and per-member read state.
- Add subscriptions, usage counters, and encrypted external-account credentials.
- Preserve existing personal ownership and introduce separate organization-scoped workspace routes; do not migrate personal data into shared workspaces.
- Generate and test migrations against local development/test databases before any production execution.

### AI configuration and prompts

- Support the three providers specified by the roadmap: OpenAI, Anthropic, Mistral.
- Keep provider/model selection configurable; require 1536-dimensional output for the chosen embedding model.
- Version prompts for summaries, tags, newsletter synthesis, social recap formats, and command intent extraction.
- Anonymize external inputs; do not log raw article payloads or personal prompts.
- Use deterministic test doubles until credentials and live API use are authorized.
- Preserve source attribution and treat ingested article text as untrusted input.

### Project MCP implementation

Implement authenticated `/mcp/` access to `search_articles`, `get_feed_summary`, `list_categories`, and `get_article`, with the same user/workspace permission boundaries as application APIs. This does not enable agent runtime MCP access or change shared agent settings.

## Product decisions and external prerequisites

- Pricing is inconsistent: landing advertises Team at EUR 29/month for up to five users, while the roadmap requests per-seat billing. Keep prices and quotas configurable; obtain the commercial decision before enabling paid checkout.
- Enterprise SSO appears in landing copy but is not a roadmap deliverable. Remove unimplemented availability claims or mark them as planned.
- Deployable code can be completed locally; production domain, hosting, provider credentials, OAuth app approvals, Stripe products/prices, email delivery settings, and analytics site identifiers require account-specific setup.
- Privacy policy and provider agreements need the actual operator, purposes, providers, and retention decisions. Technical privacy controls alone do not establish legal compliance.
- Live email, social publishing, charges, and public deployment require explicit authorization. Tests use local delivery or provider doubles.

## Verification and completion reporting

Run targeted tests per feature, then the repository's broad gates: `make quality`, `make tests`, `make tests-api`, `make front-lint`, `make front-test`, frontend `npm run typecheck`, `make front-build`, and `make landing-build`.

Report implemented behavior, passed/failed checks, migration validation, remaining account setup, and any unverified external behavior separately. Do not mark simulated integration tests as successful live integration tests.

No commits, pushes, production migrations, public deployment or paid API usage have been performed. AGENTS.md was changed at the user’s explicit request; other shared instruction assets remain unchanged.
