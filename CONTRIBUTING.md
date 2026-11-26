# Contributing to Signalist

Signalist is in active development and **not open to outside contributions
yet**. This guide describes how work is done in the repository, for the
maintainer, invited collaborators and coding agents.

## Before you start

1. Read [README.md](README.md) for the product and setup, and
   [docs/MVP.md](docs/MVP.md) for the current scope.
2. Coding agents also follow [AGENTS.md](AGENTS.md) and the shared rules in
   `.claude/rules/`.
3. Keep changes inside the MVP scope (personal weekly digest) unless the task
   is explicitly about legacy features.

## Setup

```bash
make install
docker compose exec app php bin/console lexik:jwt:generate-keypair   # first time
make front-dev
```

Podman users can replace `docker compose` with `podman compose`.

## Branches

- Never commit directly to `master`.
- One branch per change, named by type: `feat/…`, `fix/…`, `docs/…`,
  `refactor/…`, `chore/…`.
- Open a pull request to `master`; merge only once CI is green.

## Commit messages

[Conventional Commits](https://www.conventionalcommits.org/), enforced by
GrumPHP:

```
<type>(<optional scope>): <short summary>

<optional body explaining why>
```

- Types: `build`, `ci`, `chore`, `docs`, `feat`, `fix`, `perf`, `refactor`,
  `revert`, `style`, `test`.
- Subject and body lines: 72 characters maximum.
- No `var_dump(` or `die(` in committed code.

## Code conventions

**Backend (PHP 8.5, Symfony 8.1)**

- `declare(strict_types=1);` in every file.
- Flow: Controller or API Platform processor → Input DTO → Handler → Doctrine or
  port. Business rules live in handlers, domain services and message handlers;
  controllers only orchestrate.
- Slow work (RSS, AI, email, Stripe) goes through Messenger, never the request
  cycle.
- Ports in `src/Domain/*/Port/`, adapters in `src/Infrastructure/`.
- Errors use RFC 7807 problem details; never leak internal messages.
- Fix PHPStan findings in code or PHPDoc; do not add ignores or change
  `phpstan.neon` without approval.

**Frontend (React 19, TypeScript)**

- Data fetching and mutations in hooks (`frontend/src/hooks/`) with React Query.
- Every user-visible string goes through i18n, in both `en.json` and `fr.json`.
- Never render untrusted HTML without DOMPurify.

**General**

- Prefer the simplest readable solution; optimize only for a measured problem.
- Reuse nearby patterns before adding a new abstraction.
- Avoid very long one-line expressions; split them so a reviewer can read them.

## Tests

Tests ship with the change, not after.

| Change | Test |
|--------|------|
| Handler, domain service, validator | PHPUnit unit test (`tests/Unit`) |
| Persistence, Doctrine, adapters, workers | PHPUnit integration test (`tests/Integration`) |
| Endpoint behavior or API contract | Behat scenario (`features/api`) |
| Component, hook, user interaction | Vitest + React Testing Library |

- Bug fixes include a regression test when the behavior can be reproduced.
- Cover the happy path and at least one failure path (forbidden, invalid,
  not found).
- PHPUnit method names are camelCase (`testRejectsUnknownSource`).
- Backend suites share one test database: run them one at a time.

## Before opening a pull request

```bash
make quality        # CS Fixer, PHPStan level 9, Rector
make tests          # PHPUnit
make tests-api      # Behat
make front-lint
make front-test
(cd frontend && npm run typecheck)
```

Also run `make front-build` or `make landing-build` when those parts change,
and `make db-diff` to check that entity changes come with a migration.

## Pull requests

Describe:

- **what** changed and **why**
- **how** it was tested (commands and results)
- migrations, new environment variables or configuration changes
- anything not verified (for example real AI, Stripe or email)

Update `CHANGELOG.md` under `Unreleased`, and the docs when behavior or setup
changes. Reviewers use [REVIEW.md](REVIEW.md).

## Security

Never commit secrets. Report vulnerabilities privately as described in
[SECURITY.md](SECURITY.md).
