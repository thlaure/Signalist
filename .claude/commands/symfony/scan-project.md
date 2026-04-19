Review this Signalist repository and produce an implementation-ready map.

User request: `$ARGUMENTS`

Workflow:
1. Read `AGENTS.md`, `CLAUDE.md`, `README.md`, `composer.json`, `Makefile`, and `frontend/package.json`.
2. Inspect the project layout before proposing any change:
   - `src/`
   - `frontend/`
   - `config/`
   - `tests/`
3. Detect the active conventions:
   - Symfony and PHP versions
   - API Platform usage style
   - React/Vite/testing usage
   - Messenger and async flows
   - AI, RSS, MCP, and privacy-sensitive areas
   - test stack and quality gates
4. Identify the nearest existing pattern for the requested area.
5. Call out project-specific constraints that matter before coding.

Output format:
- `Context`: 4-8 bullets with relevant architecture and tooling facts
- `Existing patterns`: file paths worth mirroring
- `Files likely to change`: exact paths or tight glob patterns
- `Risks`: regressions, hidden coupling, or prerequisites
- `Implementation plan`: short numbered list

Rules:
- Prefer local project patterns over framework defaults.
- Do not invent new folders or layers when the repo already has a clear shape.
- Surface any policy in `AGENTS.md` that requires explicit confirmation before changes.
