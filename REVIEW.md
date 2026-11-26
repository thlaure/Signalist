# Code Review Guide

How to review a Signalist change, for human reviewers and review agents.
Style is handled by PHP CS Fixer, ESLint and Rector: focus on what tools cannot
catch.

## Priorities

Review in this order and report findings most severe first:

1. **Correctness** — does it do what it claims, including failure paths?
2. **Security and privacy** — can it leak, expose or corrupt data?
3. **Data safety** — migrations, idempotency, duplicate side effects.
4. **Tests** — is the changed behavior covered?
5. **Architecture** — right layer, existing patterns reused.
6. **Readability** — can a reviewer understand it quickly?

Label each finding:

- **Blocker** — must be fixed before merge (bug, security hole, data loss).
- **Should fix** — real problem, can be a follow-up if agreed.
- **Nit** — optional improvement.

Give the location (`file:line`), the concrete failure scenario, and a suggested
fix.

## Checklist

### Correctness

- [ ] Edge cases handled: empty input, missing records, limits reached,
      concurrent requests.
- [ ] Dates and periods use UTC consistently (digest cutoff is Monday 08:00 UTC).
- [ ] State transitions are valid (see the digest status table in
      [docs/MVP.md](docs/MVP.md)).
- [ ] Error responses use RFC 7807 and the right status code.

### Security and privacy

- [ ] Every endpoint requires authentication unless intentionally public.
- [ ] Every read or write is scoped to the owner; another user's resource
      returns 404.
- [ ] User input is validated before database writes, queue messages or
      outbound requests.
- [ ] User-supplied URLs go through SSRF protection; outbound calls have
      timeouts and size limits.
- [ ] No personal data (IDs, emails, names, source URLs) in AI prompts or logs.
- [ ] AI output is treated as untrusted: escaped, length-checked, never used as
      a link or a command.
- [ ] User-controlled HTML is sanitized (DOMPurify) or rendered as text.
- [ ] Responses expose only the fields the client needs.
- [ ] No secrets, tokens or credentials in code, fixtures or logs.
- [ ] Consent and processing restriction are checked before AI processing or
      email.
- [ ] New personal data is included in export and erased on account deletion
      (`PrivacyHandler`).

### Async work and side effects

- [ ] Slow work (RSS, AI, email, Stripe) runs through Messenger, not in the
      request.
- [ ] Handlers are safe to run twice (unique keys, status checks, locks).
- [ ] Retries are bounded and cannot send the same email, charge or post twice.
      Uncertain outcomes are marked, not blindly retried.
- [ ] Messenger retries and scheduler re-dispatch do not multiply each other.
- [ ] New message classes are routed in `config/packages/messenger.yaml`.

### Database

- [ ] Entity changes come with a migration (`make db-diff` shows nothing left).
- [ ] Migrations are additive or have a safe rollout plan; `down()` does not
      silently destroy shared data.
- [ ] Constraints enforce invariants (unique, not null, foreign keys with the
      right `ON DELETE`).
- [ ] Queries are bounded (`LIMIT`, pagination); no N+1 queries in loops.

### MVP scope

- [ ] The change stays within the weekly digest scope, or is explicitly about
      legacy features.
- [ ] New legacy API routes or jobs are blocked by `MvpScopeListener` when MVP
      mode is on.
- [ ] Legacy tests still run with MVP mode disabled (`config/services_test.yaml`).

### Tests

- [ ] Happy path and at least one failure path covered (forbidden, invalid, not
      found, quota reached).
- [ ] Bug fixes include a regression test.
- [ ] Right test level: unit for rules, integration for persistence and
      workers, Behat for endpoints, Vitest for UI behavior.
- [ ] Tests assert behavior, not implementation details.

### Architecture

- [ ] Controllers and processors only orchestrate; rules live in handlers or
      domain services.
- [ ] External systems are reached through ports and adapters.
- [ ] Existing patterns, hooks and API Platform features reused before new
      abstractions.
- [ ] Configuration values come from parameters or environment variables with
      validated ranges.

### Frontend

- [ ] Data loading and mutations live in hooks with React Query.
- [ ] Loading, error and empty states are handled.
- [ ] All strings translated in `en.json` and `fr.json`.
- [ ] Accessible: headings, labels, buttons that are real buttons.

### Readability

- [ ] Names say what things are; no unexplained abbreviations.
- [ ] No very long single-line expressions; complex conditions are split or
      named.
- [ ] Comments explain *why*, not *what*.
- [ ] Repeated status strings or magic values are not spread across files.

### Docs and release

- [ ] `CHANGELOG.md` updated under `Unreleased`.
- [ ] README or `docs/` updated when setup, configuration or behavior changes.
- [ ] New environment variables documented with defaults.

## Verification

Before approving, confirm CI is green or that these passed locally:

```bash
make quality && make tests && make tests-api
make front-lint && make front-test && (cd frontend && npm run typecheck)
```

Say explicitly what was **not** verified (real AI, Stripe, email, browser QA).

## Related

- [CONTRIBUTING.md](CONTRIBUTING.md) — workflow and conventions
- [SECURITY.md](SECURITY.md) — security policy and known gaps
- [AGENTS.md](AGENTS.md) and `.claude/rules/` — rules for coding agents
