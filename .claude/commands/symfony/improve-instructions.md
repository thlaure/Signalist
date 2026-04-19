Review the repository guidance and propose improvements to instruction files without editing them silently.

Scope or context: `$ARGUMENTS`

Target files:
- `AGENTS.md`
- `CLAUDE.md`
- `.claude/rules/*.md`
- `.claude/patterns.md`
- `.claude/commands/symfony/*.md`
- `.claude/skills/*/SKILL.md`

Workflow:
1. Inspect the current repository shape, quality gates, and recent workflow expectations.
2. Look for instruction drift:
   - repeated corrections in recent work
   - `Makefile`, `composer.json`, `frontend/package.json`, or repo structure changes
   - architecture or testing conventions no longer reflected in instructions
   - duplicated or conflicting guidance
3. Classify each candidate update:
   - policy
   - workflow
   - pattern
   - project fact
   - temporary note
4. Keep only durable, reusable updates. Ignore one-off preferences.
5. Produce a proposed patch and rationale.
6. Ask for confirmation before editing any instruction file.

Rules:
- Never edit instruction files silently.
- `AGENTS.md` remains the canonical source of repository-specific rules.
- `CLAUDE.md` must stay a thin pointer.
- Update `.claude` files only when the improvement is reusable and not just project noise.
- If commands and skills overlap, keep them aligned with the same underlying rules and patterns.

Output format:
- `Detected drift`
- `Proposed updates`
- `Why each update is useful`
- `Patch preview`
- `Confirmation request`
