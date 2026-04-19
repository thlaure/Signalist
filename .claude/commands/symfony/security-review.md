Perform a focused security review for a Signalist change.

Review scope: `$ARGUMENTS`

If the scope is omitted, inspect the current git diff.

Security checklist:
1. Authentication:
   - protected routes and operations are explicit
   - anonymous access is intentional
2. Authorization:
   - server-side access checks exist where needed
   - object ownership, tenant, or user boundaries are enforced
3. Input handling:
   - DTOs, validators, or frontend input boundaries constrain user input
   - identifiers, enums, URLs, uploaded data, and rich content are handled safely
4. External interactions:
   - secrets come from configuration, not code
   - outbound RSS, AI, sync, and MCP-related flows are bounded and validated
   - user-provided URLs or remote targets are handled safely
5. Data exposure:
   - only intended fields are returned or rendered
   - stack traces, tokens, prompts, and internal details are not leaked
6. Privacy:
   - personal data is minimized before external AI calls
   - logs, prompts, and fixtures do not expose sensitive user data
7. Tests:
   - negative tests exist for forbidden or invalid paths when relevant

Prioritize findings:
- auth or authz bypass
- sensitive data leaks
- unsafe input or external call handling
- privacy regressions in AI or MCP flows
- missing negative tests on protected behavior

Response format:
- findings first, ordered by severity
- then open questions or assumptions
- then a short hardening summary

Tie every finding to concrete code paths and missing or incorrect enforcement.
