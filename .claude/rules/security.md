# Security Rules

- Never hardcode secrets, tokens, or credentials.
- Enforce authentication and authorization on the server side when access is scoped.
- Validate user-controlled input before database writes, queue dispatches, or outbound calls.
- Do not send raw personal data to external AI providers.
- Keep outbound requests bounded and safe when user input influences feeds, URLs, or remote targets.
- Minimize response data to the fields actually intended for clients.
- Sanitize or safely render user-controlled HTML or rich content in the frontend.
- Preserve the repository's existing error-handling strategy instead of leaking internals.
- Add negative-path coverage for forbidden, invalid, or unsafe requests when relevant.
- Do not weaken static-analysis protections to make a warning disappear.
- Prefer fixing PHPStan findings in code, types, or PHPDoc rather than adding ignores or broadening `phpstan.neon`.

## AI and MCP Policy

- Agent runtime MCP access is blocked project-wide (`allowedMcpServers: []` in `settings.json`). Any exception requires explicit team approval.
- The project may expose its own `/mcp/` endpoints; treat them as application surface that needs explicit authz, least privilege, and careful output shaping.
- Do not expose secrets, internal-only actions, or private user data through MCP tools or AI adapters.
- Do not log private prompts, access tokens, or user data in plain text.
- When in doubt, ask before changing flows that touch authentication, external AI providers, MCP routes, or personal-data handling.
