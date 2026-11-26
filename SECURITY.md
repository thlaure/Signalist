# Security Policy

## Supported versions

Signalist is pre-release and not publicly deployed. Only the latest `master`
branch receives security fixes.

## Reporting a vulnerability

Please **do not** open a public issue for security problems.

Report privately through GitHub:
**Security → Report a vulnerability** on the
[repository page](https://github.com/thlaure/Signalist/security/advisories/new).

Include:

- what is affected (endpoint, page, worker, dependency)
- steps to reproduce or a proof of concept
- the impact you expect (data exposure, account takeover, …)

Please use test accounts and synthetic data only, and do not access or change
other people's data.

What to expect: an acknowledgement, an assessment of severity, and a fix or
mitigation plan. Credit is given in the changelog if you want it.

## Scope

In scope:

- the Symfony API (`/api/v1/*`), workers and scheduler
- the React application (`frontend/`)
- the landing site (`landing/`)
- the application MCP endpoint (`/mcp/`, disabled in the default MVP edition)
- Docker and deployment configuration in this repository

Out of scope: third-party services themselves (Stripe, Ollama, mail providers,
social networks), denial-of-service through volume alone, and findings that
require a compromised server or operator account.

## Security controls in place

| Area | Control |
|------|---------|
| Authentication | JWT (LexikJWT), email verification before login, generic responses to prevent account enumeration |
| Authorization | All data scoped to the authenticated owner; non-owned resources return 404 |
| Input | DTO validation on every write; RFC 7807 errors without internal details |
| Outbound requests | SSRF protection on user-supplied feed URLs; bounded HTTP clients with timeouts and size limits |
| AI | Weekly digest uses a local Ollama endpoint only; article text treated as untrusted in prompts; no account IDs, emails or source URLs in prompts; email links come from stored data, never model output |
| Rendering | Article HTML sanitized with DOMPurify; digest content escaped in email and rendered as text in React |
| Secrets | No secrets in the repository; external tokens encrypted (sodium secretbox / AES-GCM) with an operator-provided key |
| Payments | Stripe webhook signatures verified; test keys only by default |
| Privacy | Consent per purpose, export, restriction, deletion; see [docs/PRIVACY.md](docs/PRIVACY.md) |
| Legacy surface | `SIGNALIST_MVP_ENABLED=true` returns 404 for legacy routes and `/mcp`, and skips legacy background jobs |
| CI | PHPStan level 9, Composer and npm audits, TruffleHog secret scan, Trivy image and repository scans, Hadolint |

## Known gaps

These are tracked and should be resolved before a public launch:

- No login or registration rate limiting (only the waitlist is rate-limited).
- Registration requires 8 characters but no longer checks password strength.
- The TruffleHog CI action is referenced by branch (`@main`) rather than a
  pinned version.
- Public privacy and terms pages are drafts; operator details and supplier
  agreements are not completed.

## For contributors

Follow [`.claude/rules/security.md`](.claude/rules/security.md) and the security
checklist in [REVIEW.md](REVIEW.md). In short: never commit secrets, check
authorization on the server, validate input before writes or outbound calls,
keep personal data out of AI prompts and logs, and add tests for forbidden and
invalid requests.
