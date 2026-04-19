---
name: hotfix
description: Use this skill when the user describes a critical production issue that needs an immediate, minimal-risk fix in Signalist.
---

# Hotfix

Use this skill for urgent production-facing fixes where the blast radius must stay minimal.

Read first:
- `AGENTS.md`
- `.claude/rules/architecture.md`
- `.claude/rules/testing.md`
- `.claude/rules/security.md`

Read as needed:
- relevant logs
- the failing path

Workflow:
1. Confirm the issue is urgent and production-facing.
2. Localize the failing path quickly.
3. Choose the smallest safe fix.
4. Prefer a dedicated `hotfix/...` branch when working from a shared branch.
5. Implement only the containment or corrective change needed for the incident.
6. Add the smallest meaningful regression coverage.
7. Run the narrowest relevant verification, then broader checks if the touched area requires them.
8. Prepare commit and PR notes.
9. Ask for confirmation before any commit or push.

Rules:
- Do not refactor during a hotfix.
- If the durable fix is larger, propose a follow-up task after the incident is contained.
- Be explicit about any skipped verification.
