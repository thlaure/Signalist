# Agent Guide

Canonical agent instructions for this repository live in this file.

`CLAUDE.md` must stay a thin pointer to `AGENTS.md` so Claude and Codex share one source of truth.

## Project

- Signalist is an AI-powered RSS intelligence platform with a Symfony/API Platform backend and a React frontend
- Backend stack: PHP 8.5, Symfony 7.4, API Platform 4.x, FrankenPHP, PostgreSQL, pgvector
- Frontend stack: React 19, TypeScript, Vite 7, MUI 7, react-i18next, React Query
- Runtime integrations: Symfony Messenger, Redis, Symfony AI providers, outbound RSS and third-party syncs
- Primary route surfaces: `/api/v1/` for application APIs, `/mcp/` for project MCP endpoints, `frontend/` for the web UI

## Architecture

Use these principles pragmatically:

- keep clean architecture, hexagonal boundaries, and CQRS where the repository already uses them
- keep API Platform resources, providers, and state processors thin and orchestration-focused
- keep controllers thin when a route is not handled through API Platform
- keep business rules in handlers, domain services, and message handlers
- keep repositories and infrastructure adapters focused on persistence, external APIs, RSS parsing, AI providers, or transport concerns
- keep frontend components readable and focused on rendering plus interaction wiring
- keep frontend data-fetching, mutations, and cache orchestration in dedicated hooks or clients instead of burying them in large components

Current backend flow patterns:

- write flow: `API Platform State Processor or Controller -> Handler -> Domain Model/Port -> Infrastructure Adapter`
- read flow: `API Platform Resource/Doctrine -> normalized response`
- async flow: `Handler -> Messenger Message -> MessageHandler -> Infrastructure Adapter`

Current frontend flow pattern:

- `Route/Page -> query or mutation hook -> API client -> backend`

## Repository Structure

```text
src/
├── Domain/
│   ├── Article/
│   ├── Auth/
│   ├── Bookmark/
│   ├── Category/
│   └── Feed/
├── Entity/
├── Infrastructure/
│   ├── ApiPlatform/
│   ├── Auth/
│   ├── EventListener/
│   ├── Persistence/
│   ├── RSS/
│   └── Validator/
└── UI/
    └── Controller/

frontend/
├── src/
├── package.json
└── vite.config.ts

tests/
├── Unit/
├── Integration/
└── Behat/
```

## Engineering Rules

Always:

- keep `declare(strict_types=1);` in PHP files
- prefer explicit naming and predictable local patterns over clever abstractions
- follow SOLID pragmatically; do not add abstraction layers without a concrete reason
- if API Platform already provides the requested behavior cleanly, prefer the built-in API Platform feature over extra architectural indirection
- if React Query, React Router, or the existing frontend structure already provides the requested behavior cleanly, reuse it instead of adding a new frontend abstraction
- keep RSS crawling, AI inference, newsletter generation, and other potentially slow outbound work asynchronous when the existing Messenger flow applies
- write tests for behavior changes in the same session
- run verification after changes
- prefer readability, reviewability, and testability over premature optimization
- if performance and readability conflict and there is no measured bottleneck, choose readability
- prefer fixing PHPStan issues in code, types, or PHPDoc instead of changing `phpstan.neon`
- keep frontend strings translatable and aligned with the project i18n setup
- keep the final result easy for a human reviewer to understand quickly

Execution principles:

- think before coding: state assumptions that materially affect the implementation
- simplicity first: prefer the minimum implementation that fully solves the request
- surgical changes: touch only what the request and its verification require
- reuse nearby patterns before inventing a new structure
- bug fix: reproduce first when practical, then add a regression test
- feature work: add the right test level instead of only broad smoke coverage
- refactor: verify behavior before and after with the relevant checks

Ask first:

- adding composer or npm packages
- changing PostgreSQL schema or pgvector dimensions
- changing AI provider configuration or prompt contracts
- changing the project MCP protocol implementation
- changing `phpstan.neon`
- changing shared instruction files
- running `git commit`
- running `git push`

Never:

- commit directly to `main`, `master`, or `develop`
- hardcode secrets, tokens, or credentials
- perform blocking HTTP, RSS, or AI calls in the request cycle when the existing async flow applies
- send raw personal data to external AI services
- leak internal errors, tokens, or private data in responses, logs, prompts, or fixtures
- duplicate project instructions across `AGENTS.md` and `CLAUDE.md`
- run `git commit` or `git push` silently; always ask for confirmation in the current conversation first

## Shared `.claude` Assets

Claude and Codex must both use the repo-local `.claude/` folder as shared operational guidance.

Use these files as the common behavior layer:

- `.claude/settings.json`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`
- `.claude/rules/security.md`
- `.claude/patterns.md`

Use the matching workflow when the task fits:

- repository scan or inspection: `.claude/skills/scan-project/SKILL.md` or `.claude/commands/symfony/scan-project.md`
- new functionality: `.claude/skills/new-feature/SKILL.md` or `.claude/commands/symfony/new-feature.md`
- bug fixing: `.claude/skills/bug-fix/SKILL.md` or `.claude/commands/symfony/bug-fix.md`
- general review: `.claude/skills/review-change/SKILL.md` or `.claude/commands/symfony/review-change.md`
- security review: `.claude/skills/security-review/SKILL.md` or `.claude/commands/symfony/security-review.md`
- verification and checks: `.claude/skills/verify-quality/SKILL.md` or `.claude/commands/symfony/verify-quality.md`
- commit preparation: `.claude/skills/prepare-commit/SKILL.md` or `.claude/commands/symfony/prepare-commit.md`
- instruction improvement: `.claude/skills/improve-instructions/SKILL.md` or `.claude/commands/symfony/improve-instructions.md`
- production-urgency fixes: `.claude/skills/hotfix/SKILL.md` or `.claude/commands/symfony/hotfix.md`
- execution discipline for review, refactor, or ambiguity-heavy tasks: `.claude/skills/karpathy-guidelines/SKILL.md`

Guidance:

- skills and commands are two interfaces for the same workflows; do not let them drift
- prefer skills when the user is speaking naturally
- prefer commands when the user explicitly invokes a named workflow
- rules and patterns are the shared source of truth behind both interfaces
- `.claude/settings.json` is the versioned repository-default settings file for both Claude and Codex
- `.claude/settings.local.json` is only for optional local overrides and must not be treated as the shared team standard

## AI, MCP, and Privacy Policy

- agent runtime MCP access is blocked by default in `.claude/settings.json`; any exception requires explicit team approval
- the repository may implement its own `/mcp/` endpoints; treat them as application attack surface, not as permission to use external MCP servers
- do not expose destructive internal actions, secrets, or private user data through MCP tools or AI adapters
- anonymize or minimize user data before external AI calls
- preserve GDPR-oriented deletion, retention, and purpose-limitation expectations when changing flows involving personal data

## Instructions Improvement Policy

Instruction files are living documentation and should improve with the project and environment, but only through an explicit proposal-and-confirmation workflow.

Files in scope:

- `AGENTS.md`
- `CLAUDE.md`
- `.claude/rules/*.md`
- `.claude/patterns.md`
- `.claude/commands/symfony/*.md`
- `.claude/skills/*/SKILL.md`

Policy:

- instructions may be improved when there is durable evidence of drift
- examples of drift:
  - repeated corrections or reviewer comments
  - `Makefile`, `composer.json`, or frontend command changes
  - architecture or testing conventions that changed in practice
  - duplicated or conflicting guidance
- only reusable, stable guidance should be added
- temporary context, one-off fixes, and local anecdotes should not be added to instruction files
- changes to instruction files must be proposed first and applied only after explicit confirmation in the current conversation

## Quality Gates

Run when relevant:

- `make lint`
- `make analyse`
- `make rector`
- `make tests-unit`
- `make tests-integration`
- `make tests`
- `make tests-api`
- `make front-lint`
- `make front-test`
- `npm run typecheck` from `frontend/` when frontend TypeScript changes
- `make grumphp` when the broader pre-commit gate is required

Preferred full verification for broad backend/frontend changes:

- `make quality`
- `make tests`
- `make tests-api`
- `make front-lint`
- `make front-test`
- `npm run typecheck` from `frontend/`

## Testing Notes

- backend unit tests: `tests/Unit`
- backend integration tests: `tests/Integration`
- API behavior tests: `tests/Behat`
- frontend behavior tests: `frontend/src/**/*.test.tsx`
- PHPUnit method names must stay camelCase

## Documentation Policy

Use this split:

- `README.md`: human-facing project overview and usage
- `AGENTS.md`: canonical agent instructions
- `CLAUDE.md`: pointer file only

If agent instructions need to change:

1. update `AGENTS.md`
2. keep `CLAUDE.md` minimal and referential
3. update `README.md` only for human-facing behavior or workflow changes
