# Roadmap implementation review

## What and why

Signalist previously provided RSS reading, categories, bookmarks and authentication. This change implements the remaining provider-independent product flows: privacy controls, article intelligence pipelines, full-text and vector search, sourced Q&A, tags, newsletters, shared workspaces, billing, account connections, bookmark synchronization, social drafts and publishing, Spotlight, and authenticated read-only MCP tools. The English/French landing page now has a confirmed, withdrawable waitlist and describes actual availability.

The user authorized autonomous local implementation and requested that OpenAI setup be deferred. `AGENTS.md` records that execution policy while retaining approval requirements for commits, pushes, production changes and live external actions.

## How

- Keep application APIs scoped to the authenticated owner or authorized workspace member. Preserve personal ownership instead of migrating personal content into shared workspaces.
- Use domain handlers, provider ports and Messenger jobs for slow work. Persist scheduling, billing, synchronization and publication state; stop uncertain outbound actions instead of blindly duplicating them.
- Add consent, processing restriction, portable export, account erasure, retention and minimized audit records. Encrypt external credentials and OAuth SDK sessions; bind callbacks to authorization state.
- Store embeddings as PostgreSQL `vector(1536)` with cosine and full-text indexes. Validate provider output and minimize article text before external processing. Unconfigured AI adapters report their unavailable state; deterministic content flows remain usable.
- Add translated React pages and dedicated query/mutation hooks. Provide deployment configuration, configuration guidance and privacy documentation drafts.
- Update vulnerable dependencies, including the landing page's upgrade to Astro 7. Node 24 is used in CI and production builds. Add offline Bluesky SDK tests to CI and keep health, mapping and coverage checks enforced.

## Verification

Local checks completed on 2026-10-13:

| Check | Result |
| --- | --- |
| Backend quality | `make quality RECTOR_FLAGS=--debug` passed: formatting, PHPStan and serial Rector |
| Backend behavior | `make tests`: 193 tests, 769 assertions passed |
| Domain statement coverage | 82.65% (1,515 of 1,833 statements); the 80% CI threshold is unchanged |
| API contracts | `make tests-api`: 85 scenarios, 641 steps passed |
| Frontend | 42 tests passed after route splitting; ESLint, TypeScript and production build passed |
| Bluesky SDK bridge | Five offline tests passed: metadata, record IDs, fixed errors, ciphertext binding and discovery restrictions |
| Landing | Six English/French pages built; internal links checked |
| Dependencies | PHP audit found no vulnerabilities or abandoned packages; all three JavaScript dependency trees audited with no vulnerabilities |
| Doctrine | Mapping validation passed; database synchronization was deliberately skipped because custom PostgreSQL indexes are managed by migrations |
| Migrations | Fresh disposable database migrated through all 18 versions. Rollback/reapply passed through version `20261012001300`; the final Q&A migration then applied successfully |
| Deployment configuration | Production Compose and Caddy configuration validated; CI YAML parsed with duplicate-key rejection |
| Patch hygiene | `git diff --check` passed |

The frontend now loads pages and the authenticated layout on demand. The largest JavaScript chunk fell from 877.09 KB (274.42 KB gzip) to 495.59 KB (163.68 KB gzip); the bundle-size warning is gone. Navigation remains mounted while route content loads. These figures compare the largest emitted chunk, not the aggregate initial download.

Removed unused `behatch/contexts`, replaced `friends-of-behat/mink-browserkit-driver` with maintained `behat/mink-browserkit-driver` 2.3, and replaced the Laminas HTTP transport with existing Symfony HttpClient. This removes transitive `laminas/laminas-loader` while retaining Laminas Feed parsing. Behat prerequisites are now explicit dependencies. Composer removed 11 packages and installed one without upgrading unrelated packages. Normal `composer audit` now blocks abandoned packages in CI.

RSS fetching has a 20-second total duration, three-redirect limit, 4 MiB response limit and private-network protection. Six regression tests cover RSS, Atom, failed HTTP responses, oversized responses, malformed XML and private-network rejection. A live public feed returned 10 articles. API database resets explicitly require a disposable `_test` database.

Production preview browser checks passed for login, inbox, settings and newsletters against the existing Podman backend. App, Messenger and scheduler container IDs and start times remained unchanged. See [follow-up browser evidence](BROWSER-QA.md#bundle-and-dependency-follow-up).

## Risks and follow-up

- Live AI adapters, Symfony AI provider setup, credentials and provider-specific summary/tagging prompts remain deferred. Semantic search and generated answers require configured providers.
- Input minimization removes common identifiers and known names; arbitrary-name NER is incomplete. It does not guarantee anonymity. Review this boundary before enabling an external AI provider.
- Raindrop OAuth rejects callbacks without matching response state. Its published contract does not document state echo; verify support in staging before enabling that connection.
- Stripe, LinkedIn, X, Bluesky and Raindrop adapters are covered locally with test doubles or offline SDK tests. Real accounts, approved scopes, public callbacks, webhook setup, commercial prices and actual provider behavior need staging validation.
- Newsletter delivery and external publishing need configured services and explicit authorization for live actions. Uncertain outcomes require operator inspection before retrying.
- Production image construction remains unverified: Docker Hub downloads failed with network/socket errors. Build and scan the complete image before deployment.
- Operator identity, legal text, supplier agreements, hosting/domain configuration, backups and restore rehearsal remain launch work. Technical privacy controls are not a legal compliance determination.
- Production deployment, production migrations, paid API use, real charges and social publication were not performed. No commit or push was made.

See [roadmap](ROADMAP.md), [deployment guide](DEPLOYMENT.md) and [privacy guidance](PRIVACY.md) for detailed status and configuration.

Browser QA subsequently exercised the available local UI flows and fixed quota feedback, login server-detail rendering, check-email markup and the waitlist email label. See [browser QA report](BROWSER-QA.md) for evidence and limits.
