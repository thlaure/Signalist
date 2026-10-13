# Deployment and integration configuration

The application and workers run locally. Public deployment, production migrations, real email delivery, payment operations and live social posting have not been executed. OpenAI setup was explicitly deferred; all external AI adapters currently report configuration pending.

## Local development

Start Docker services with `SIGNALIST_HTTP_PORT=18000 SIGNALIST_HTTPS_PORT=18443 rtk docker compose up -d`. Run `rtk make db-migrate`, then `SIGNALIST_HTTP_PORT=18000 rtk make front-dev`. The standard setup uses port 8000. Passing `SIGNALIST_HTTP_PORT=18000` to both Compose and Vite keeps their development URLs aligned; `SIGNALIST_DEV_BACKEND_URL` can override the proxy target. Mailpit is at `http://localhost:8025`. The scheduler dispatches newsletter, billing and connection jobs; the worker consumes `oauth_priority` and `async`.

Run `rtk make quality`, `rtk make tests`, then `rtk make tests-api`. Backend test suites share a disposable database and must run sequentially. If Rector workers time out, use `rtk make quality RECTOR_FLAGS=--debug` to run the same Rector checks serially. Frontend checks: `rtk make front-lint`, `rtk make front-test`, `rtk npm run typecheck` from `frontend/`, and `rtk make front-build`. Landing: `rtk make landing-build` with Node 22.19+ (Node 24 recommended). Landing uses patched Astro 7; see its [upgrade guide](https://docs.astro.build/en/guides/upgrade-to/v7/). Offline Bluesky: `rtk docker compose exec app node --test integrations/bluesky/*.test.mjs` (Node 22.19+; container uses Node 24).

## Production topology

`docker/frankenphp/Dockerfile` builds the React SPA and installs production PHP and Bluesky dependencies. `docker-compose.prod.yml` is a standalone topology with HTTPS, internal PostgreSQL/Redis, workers, scheduler and persistent volumes. It does not expose PostgreSQL, Redis or development email tools. No migration runs automatically on startup.

Provide a protected environment file outside the repository and set `SIGNALIST_PRODUCTION_ENV_FILE` to its absolute path. Set `SIGNALIST_JWT_DIR` to a protected directory containing `private.pem` and `public.pem`. Pass database initialization variables through a protected Compose `--env-file` or process environment. Never place credentials in tracked files, build arguments, frontend variables or logs.

Required application settings:

| Setting | Purpose |
| --- | --- |
| `APP_DOMAIN` | Public application hostname for Caddy HTTPS |
| `APP_SECRET` | Random application signing secret |
| `DATABASE_URL` | PostgreSQL URL; host `postgres` in this topology |
| `POSTGRES_PASSWORD` | Database initialization secret for Compose |
| `MESSENGER_TRANSPORT_DSN`, `REDIS_URL` | Internal Redis transport and cache endpoints |
| `MAILER_DSN`, `SIGNALIST_MAIL_SENDER` | Authorized production transport and verified sender address |
| `FRONTEND_URL` | Public SPA URL, same origin as API for the supported OAuth cookie flow |
| `SIGNALIST_PUBLIC_API_URL` | Public HTTPS backend origin and OAuth metadata base |
| `JWT_PASSPHRASE` | Passphrase matching mounted signing key |
| `CORS_ALLOW_ORIGIN` | Anchored allowlist regex for actual trusted origins |

Set `APP_ENV=prod`, `APP_DEBUG=0`. JWT key paths are set by Compose to `/run/keys/private.pem` and `/run/keys/public.pem`. Mount keys read-only. Keep TLS verification enabled; the Bluesky bridge explicitly enforces it. Configure verified sender identity before real newsletter or invitation delivery.

Before an approved deployment: build and scan the image, back up PostgreSQL, prove restore, review migration SQL and retained vector indexes, plan ANALYZE/IVFFlat rebuild after substantial initial data loading, configure DNS/TLS, run migrations once, then start app/workers/scheduler. Validate registration/verification, member boundaries, failed-job handling, provider callbacks, webhook signatures and expiry behavior in staging. Keep previous image and database backup available; do not downgrade migrations that would erase shared content. Redis uses append-only persistence; job records also provide recovery outboxes.

Monitor worker exits, queue/failure depth, scheduler heartbeat, DB capacity, backups, OAuth expiry and uncertain publication/delivery states. `/health` is a liveness endpoint, not proof of database, queue or email readiness. Review errors without exposing provider payloads or tokens. Do not blindly retry uncertain social posts, email sends or Raindrop creates: inspect remote outcome first.

## External account setup

`CREDENTIAL_ENCRYPTION_KEY` must be a base64-encoded 32-byte random key. Losing it requires reconnecting accounts; rotating it requires a controlled ciphertext migration. PHP tokens use sodium secretbox; Bluesky sessions use AES-GCM with owner, store kind and record key authenticated as context. No keys have been provisioned by this implementation.

| Provider | Configuration | Callback |
| --- | --- | --- |
| Raindrop.io | `RAINDROP_CLIENT_ID`, `RAINDROP_CLIENT_SECRET` | `/api/v1/connections/raindrop/callback` |
| LinkedIn | `LINKEDIN_CLIENT_ID`, `LINKEDIN_CLIENT_SECRET`, optional `LINKEDIN_API_VERSION` (default `202605`) | `/api/v1/connections/linkedin/callback` |
| X | `X_CLIENT_ID`, `X_CLIENT_SECRET` | `/api/v1/connections/x/callback` |
| Bluesky | `BLUESKY_CLIENT_PRIVATE_KEY` (ES256/P-256 PKCS8 PEM or compatible JWK), public HTTPS backend | `/api/v1/connections/bluesky/callback` |

Register exact redirect URIs and appropriate approved scopes/products with each provider. Non-Bluesky callbacks must echo the exact authorization state; missing state is rejected even when a browser cookie exists. Raindrop’s [published OAuth response contract](https://developer.raindrop.io/v1/authentication/token) does not document state echo, so confirm that support in staging before enabling its OAuth connection. A cookie-only fallback is deliberately unsupported. LinkedIn and X posting adapters follow their official [Posts API](https://learn.microsoft.com/en-us/linkedin/marketing/community-management/shares/posts-api?view=li-lms-2026-05) and [Create Posts API](https://docs.x.com/x-api/posts/create-post). Provider access, fees and live behavior remain unverified.

Bluesky uses the official AT Protocol SDK for confidential OAuth, PAR, PKCE, DPoP, session refresh and `putRecord`. Public metadata is at `/oauth/bluesky/client-metadata.json`, public keys at `/oauth/bluesky/jwks.json`. Discovery restricts HTTPS, public addresses, DNS-pinned connections, redirects, deadlines and response sizes. Publication uses an SDK-generated stable record key. No app-password fallback is implemented. Changing the signing key requires updating public metadata and provider sessions safely.

Raindrop sync imports and exports bookmarks in checkpointed batches. Local changes win simultaneous conflicts. Remote-only changes update imported article titles/URLs, without rewriting RSS source metadata. Local deletions stay local; remote bookmarks are never deleted by sync. Notes are excluded unless explicitly selected. A lost remote-create response leaves an uncertain status to prevent blind duplication. Disconnect erases local authorization; users should also revoke application access in provider settings.

## Billing and quotas

Set `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_TEAM_PRICE_ID`, and optionally `STRIPE_ENTERPRISE_PRICE_ID`. Price IDs must represent recurring per-seat prices. The final checkout price is authoritative; landing paid prices are unannounced. Configure the billing portal and point signed subscription webhooks at `/api/v1/billing/webhook`. Subscription reconciliation reads authoritative current provider state; expired entitlements fall back to Free.

Defaults: Free 5 RSS feeds / 1 member / 100 daily imported articles / no AI calls; Team 30 feeds / 5 members / unlimited article intake / 1,000 monthly AI calls; Enterprise unlimited feeds/members/article intake / 10,000 monthly AI calls. Override `plan_limits` through deployment configuration after product review. AI quotas count provider operations, not tokens or currency. Seat changes enqueue Stripe quantity updates with prorations. Active subscriptions must be cancelled before deleting their billed account/workspace.

## AI, privacy and retention

Implement configured adapters for `ArticleIntelligenceInterface`, `RecapIntelligenceInterface` and `ArticleQuestionInterface` before enabling AI. Enrichment stores separate AI summary, tags and `vector(1536)` chunks; semantic/hybrid search requires embeddings of exactly that dimension. Provider inputs exclude private bookmark notes and minimize common identifiers and known names. Arbitrary-name recognition and complete anonymization are not guaranteed. AI output remains source content requiring human review.

`app:privacy:retention` defaults to dry-run. `--apply` deletes eligible records; review counts, backups and policy first. The daily job runs only when `SIGNALIST_RETENTION_ENABLED=1`. Personal and shared bookmarks preserve articles. Backups, logs, failure queues and third-party data require separate operator policies. Public privacy/terms pages are deployment drafts and must be completed with real operator identity, contacts, legal bases, recipients and agreements before collecting production data.

## Landing and MCP

For local port 18000, build with `PUBLIC_API_BASE_URL=http://localhost:18000/api/v1`. Build landing with `PUBLIC_SITE_URL` set to the actual public origin and `PUBLIC_API_BASE_URL` set to its API `/api/v1` base. Set optional `PUBLIC_CONTACT_EMAIL`. Deploy `landing/dist` to an approved static host. Analytics are off by default; optional `PUBLIC_PLAUSIBLE_SCRIPT_URL` accepts the site's current `https://.../js/pa-....js` URL from [Plausible installation settings](https://plausible.io/docs/plausible-script). It instruments public landing pages only and receives no application content from this code.

MCP is authenticated at `/mcp/`, scoped to the same user, read-only and restricted to configured hosts/origins. It provides `search_articles`, `get_article`, `get_feed_summary`, and `list_categories`. This application endpoint does not enable agent-runtime MCP access; shared agent settings still block external MCP servers.
