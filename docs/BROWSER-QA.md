# Browser QA — 2026-10-13

Testing uses the Codex in-app browser against the local React app, built landing pages and Mailpit. Accounts and content are synthetic (`example.test` addresses). Existing accounts and content were not edited. External AI, payment and social credentials remain unconfigured; no real email, payment or publication was sent.

## Results observed in the browser

| Area | Observed result |
| --- | --- |
| Authentication | Invalid fixture credentials rejected. Synthetic registration validates password mismatch, queues verification email, supports resend, denies login before verification, accepts the signed verification link and permits subsequent login. Logout clears access. |
| Onboarding/categories | Empty-account onboarding shown. First-category dialog creates a category and slug. Spotlight also creates a category and navigates to it; its add-feed command queues a successful public RSS crawl. |
| RSS | Official Symfony feed added through Spotlight; background crawl updates the fetched timestamp and imports 10 articles visible in the inbox. A 404 source produces a safe Error state. |
| Reading | Inbox lists seeded articles; marking read persists into article detail and after reload. Article content renders without the injected script text. Source links and share menu available. |
| Bookmarks/tags | Bookmark appears in saved list. Article tag assignment, catalog creation, filtering and rename exercised; filtered result points to the matching article. |
| Search/Spotlight | Full-text search returns the correct seeded PostgreSQL article. Command overlay works; category command succeeds. AI question/enrichment requests show configuration/consent failure feedback. |
| Newsletters | Manual newsletter saved; asynchronous draft reaches Draft ready. HTML preview groups the saved article by category and retains its source URL. Send without consent is denied. After granting consent, delivery reaches Sent and the message appears in local Mailpit. A custom 72-hour interval persists and shows the next delivery date; the QA newsletter was restored to Manual afterward. |
| Workspaces | Workspace, shared category and shared feed created. Shared categories stay outside the personal sidebar. Shared article detail renders. Shared bookmark, annotation and activity entries persist. |
| Membership/access | Free plan blocks a second member. Temporary local Team entitlement allows a synthetic reader invitation, delivered to Mailpit and accepted by the matching verified account. Reader cannot manage feeds; personal inbox remains empty. Reader read state does not mark the administrator's article read. Last-administrator demotion is rejected; another member can become curator. |
| Privacy | Profile display name saved. Newsletter consent persists. Restriction disables optional consent controls; resuming restores them. JSON export downloaded and checked for seeded articles/bookmark and absence of profile credentials. Account deletion requires a password and explicit final action; that final action was not executed. |
| Social | Provider-independent recap reaches Draft, includes source link, supports editing, copying to clipboard and saving. Publication disabled without a connected account. |
| Connections | Raindrop, LinkedIn, X and Bluesky show configuration pending and disabled connection controls. Successful live OAuth/sync cannot be tested without provider setup. |
| Billing | Personal Free and workspace Team scopes show separate quotas and seat counts. Unconfigured checkout and portal controls disabled. No payment attempted. |
| Waitlist/landing | Browser submission succeeds through a local same-origin API proxy; confirmation mail appears in Mailpit; signed confirmation succeeds. French landing navigation and privacy page render correctly. Withdrawal's final erasure action was not executed. |
| Translation | English/French application controls and workspace annotation views render translated labels. |

## Fixes made during QA

- Workspace quota failures now explain the plan limit rather than suggesting invalid permissions. Reproduced again in the browser on a new Free workspace; regression tests cover plan-limit and unexpected errors.
- Login displays a generic message for HTTP 500/503 instead of exposing server exception details. Regression tests cover both statuses.
- Check-email translations render their intended emphasis rather than displaying literal `<strong>` markup. A regression test checks the rendered email element.
- Landing waitlist email field now has a translated accessible label. Rebuilt markup was inspected in the browser.

Frontend verification after the follow-up fixes: 40 tests passed; production build passed. The build retains a non-blocking bundle-size warning. ESLint, TypeScript and `git diff --check` also passed. The six-page landing build passed after the accessible-label change. The targeted MCP integration test passed with nine assertions.

## Limits and environment interruptions

- The first public RSS test URL returned HTTP 404 and produced the expected safe Error state. The feed linked from Symfony’s official blog then crawled successfully and its 10 articles were verified in the browser.
- Container project mounts became unreadable (`Operation not permitted`). Recreating only Signalist containers did not restore them. QA continued with installed host PHP, a Redis extension built only in temporary storage, and loopback URLs; global PHP configuration was not changed. The user restarted Podman, restoring mount access. Normal Signalist containers were restarted and the temporary host PHP server/worker stopped; final RSS checks ran against the normal container runtime.
- In-app browser download observation stalled once; after recovery the export downloaded successfully. A later delivery click timed out, but its result was checked before retrying: it had already reached Sent, preventing duplicate delivery.
- Direct cross-origin landing requests failed in the in-app browser. Local API requests returned 202 with the expected CORS headers. The temporary landing proxy establishes that the form, backend and confirmation flow work together, but does not validate a public deployment's cross-origin transport.
- The first viewport attempt inspected the wrong tab and remained at 1280px. The follow-up applied the override to the original QA tab and verified a real 390px viewport. The French screenshot from the first pass remains a desktop view.
- Direct MCP navigation was blocked by the browser client. The local MCP integration test independently verified authentication and owner boundaries (one test, nine assertions).
- Live AI inference, real OAuth, Raindrop synchronization, Stripe checkout/webhooks and external social publication remain credential-dependent checks. The scheduled-newsletter follow-up below exercises the existing scheduler/worker end to end. MCP protocol behavior is covered by its local integration test; browser inspection alone does not prove it.
- Irreversible account/content deletion and waitlist erasure were not executed in this browser pass. Existing automated API tests cover deletion behavior.

Screenshots are retained locally in `var/browser-qa/`: shared workspace/annotation, French desktop view, corrected quota feedback, and the successfully crawled RSS inbox. Synthetic QA data remains available for review.

## Follow-up on the current Podman environment

The continuation kept the running Podman containers and configuration in place: no starts, stops, restarts, rebuilds, migrations, dependency installation or runtime replacements. App, Messenger and scheduler container identities/startup times stayed unchanged.

- Created a separate `QA Scheduler Probe` newsletter through the UI and verified the worker's delivery target was local Mailpit. Only that synthetic record's due timestamp was moved into the past. The existing scheduler picked it up, the worker generated and delivered it, the browser showed Sent, and the email appeared in current Mailpit. The record was restored to Manual and its next-delivery indicator disappeared.
- Tested the original browser tab at 390px. Mobile navigation opens and routes correctly. Inbox, search, settings, social drafts, connections, billing, tags, feeds and workspace controls fit the viewport. Loaded inbox content also measures 390px with no document overflow. Desktop sizing was restored afterward.
- Fixed an unlabeled mobile navigation button using translated text and `aria-expanded`; added a navigation activation regression test.
- Reproduced workspace horizontal overflow (629px content in a 390px viewport). Constrained the main flex area with `minWidth: 0` and an explicit mobile width; the same workspace now measures 390px and keeps its controls visible.
- Final checks: 40 frontend tests, ESLint, TypeScript and production build passed; `git diff --check` passed. The non-blocking bundle-size warning remains.

Additional local evidence: `mobile-newsletter.jpg`, `mobile-workspace.jpg`, `mobile-inbox.jpg`, `mobile-results.json` and `scheduled-mail.jpg` in `var/browser-qa/`.


## Bundle and dependency follow-up

On 2026-10-13, production pages and the authenticated layout were split into on-demand chunks. The largest emitted JS chunk decreased from 877.09 KB to 495.59 KB (gzip 274.42 KB to 163.68 KB). The build no longer reports its bundle-size warning. Earlier entries above describe the historical build before this follow-up.

A production preview on port 4173 used the current Podman backend on port 18000. Synthetic QA sign-in, inbox, settings and newsletters loaded successfully; the navigation stayed available during page loading. Screenshot: `var/browser-qa/bundle-preview.jpg` (local ignored evidence).

All three abandoned Composer dependencies were removed or replaced. Normal Composer audit and strict manifest validation passed. Symfony HttpClient fetched a live public RSS feed with 10 articles. Six new RSS tests and two route tests bring totals to 193 backend tests / 769 assertions, 85 API scenarios / 641 steps and 42 frontend tests. PHP formatting, PHPStan, Rector, frontend ESLint, TypeScript and production build passed. The existing app, Messenger and scheduler container IDs and start times remained unchanged; no containers were restarted or reconfigured.
