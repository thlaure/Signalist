# Privacy operations and supplier agreement draft

This is implementation documentation and an operator completion template. It does not establish that a deployment complies with applicable law or that any supplier agreement has been executed.

## Implemented controls

Authenticated Settings and `/api/v1/user/*` endpoints support display-name correction, JSON export, purpose-specific consent, consent withdrawal, processing restriction and password-confirmed account deletion. Exports include owned reading data, tags, newsletters, search/answer history, social drafts/publications, consent history and access metadata; they exclude passwords, signing material, authorization codes, OAuth/session tokens and other users' private records.

Deleting an account erases personal feeds/articles/bookmarks, private annotations, searches, drafts, external credentials, SDK sessions and consent/audit records. A restricted anonymized tombstone prevents stale JWT access until physical deletion. Shared content with remaining members stays in its workspace; attribution is anonymized and administration must be transferred when needed. Active subscriptions must be cancelled first. Seat updates are queued after membership changes. Remote public posts, payment records and third-party copies require separate operator/provider handling.

AI, RSS, newsletters, synchronization and publishing recheck active ownership, processing restriction or applicable consent at their processing boundaries. External AI ports receive minimized source text and known names/common identifiers are removed. This does not detect every person name or every sensitive datum. Private bookmark notes never enter AI prompts. Raindrop notes leave the application only when the user selects note sync. Social publishing requires approval of the actual text and a currently matching connected account identity.

PHP authorization values use sodium secretbox. Bluesky SDK stores use AES-GCM with authenticated owner/kind/record context. Encryption keys are operator-provisioned; no key is generated or stored in this repository. HTTPS verification remains enabled. Logs and access audit records use metadata rather than raw provider tokens or AI inputs.

Optional retention: 90-day unbookmarked articles (both personal and shared bookmarks protect them), expired OAuth attempts/sessions, seven-day unconfirmed waitlist entries, 30-day access events and deleted-account tombstones. It defaults to disabled/dry-run; the operator must authorize and enable it. Failure queues, infrastructure logs, backups and remote services need additional retention and deletion procedures.

## Weekly digest (MVP edition)

Digest generation requires a verified email, unrestricted processing and both `newsletter` and `external_ai` consents granted before the weekly cutoff (Monday 08:00 UTC). Consent is checked again immediately before inference and before sending; withdrawal cancels the edition. Inference uses a local Ollama endpoint only, so article text does not leave the deployment. Prompts contain cleaned, anonymized article titles and text; account IDs, email addresses and source URLs are excluded. Stored editions keep titles, links, source names and summaries; they are included in the JSON export and erased with the account. Removing a source deletes its articles but keeps already archived editions.

## Operator and supplier register to complete before public launch

| Field | Required deployment decision |
| --- | --- |
| Controller/operator | Legal identity, address, privacy contact, representative/DPO where applicable |
| Purposes/legal bases | Authentication, reading, collaboration, optional processing, marketing and billing |
| Hosting/backup | Provider, locations, access controls, backup lifetime and restore/deletion policy |
| Email supplier | Provider, sender identity, delivery logs, retention and deletion process |
| AI supplier | Weekly digest: local Ollama, no external supplier. Other AI features deferred; record exact provider/model, input categories, purposes, training use, retention, locations and agreement before enabling |
| Stripe | Contract/account configuration, payment roles, statutory retention and request handling |
| Raindrop/LinkedIn/X/Bluesky | User-authorized functions, scopes, retained data, revocation and remote deletion process |
| Analytics | Off by default; record configured Plausible deployment and public-page data collection if enabled |
| Security incidents | Responsible contacts, detection, containment, notification and evidence retention procedure |

For each processor agreement, document subject matter/duration, nature and purpose, data categories, data subjects, documented instructions, confidentiality, security, subprocessors, assistance with rights/incidents, return/deletion and audit arrangements. Assess any international transfer mechanism for the actual deployment. These entries require actual contracts and operator decisions. The official [GDPR text](https://eur-lex.europa.eu/legal-content/EN/TXT/?uri=CELEX%3A32016R0679) and [EU controller/processor standard clauses](https://commission.europa.eu/publications/standard-contractual-clauses-controllers-and-processors-eueea_en) provide the primary reference framework.

Complete the public English/French privacy and terms drafts with these deployment details before accepting production registrations or waitlist requests. Exercise deletion/export/restriction and provider revocation in staging using non-sensitive test data; verify backup handling separately.
