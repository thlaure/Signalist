# Signalist Agent Router

This file defines which agent handles what, how agents coordinate, and escalation paths.

---

## Quick Reference

| Task Type | Primary Agent | Support Agents | Notes |
|-----------|---------------|----------------|-------|
| New API endpoint | `@engineer` | `@reviewer` | Follow CQRS flow |
| React component | `@frontend` | `@reviewer` | Use MUI, strict TypeScript |
| LLM/Embedding work | `@ai-specialist` | `@engineer` | Always async via Messenger |
| pgvector queries | `@ai-specialist` | `@infra` | Check index performance |
| Docker/CI changes | `@infra` | - | Requires user approval |
| Database schema | `@engineer` | `@infra` | Migration + approval required |
| Code review | `@reviewer` | - | Runs after implementation |
| Cross-cutting feature | `@engineer` | `@frontend`, `@ai-specialist` | Coordinate via workflow |
| Marketing content | `@marketer` | - | Align with engineering reality |

---

## Agent Roster

| Agent | File | Expertise |
|-------|------|-----------|
| `@engineer` | `roles/engineer.md` | PHP, Symfony, CQRS, Domain logic |
| `@frontend` | `roles/frontend.md` | React, TypeScript, MUI, Accessibility |
| `@ai-specialist` | `roles/ai-specialist.md` | Symfony AI, pgvector, MCP, LLM prompts |
| `@infra` | `roles/infra.md` | Docker, PostgreSQL, Redis, CI/CD |
| `@reviewer` | `roles/reviewer.md` | Code quality, security, architecture |
| `@marketer` | `roles/marketer.md` | Content, positioning, growth |

---

## Domain Contexts

Before working on a feature, agents MUST read the relevant domain context:

| Domain | Context File | Key Concepts |
|--------|--------------|--------------|
| Feed | `domains/feed/context.md` | RSS parsing, crawling, categories |
| Article | `domains/article/context.md` | Content extraction, metadata |
| Search | `domains/search/context.md` | pgvector, semantic similarity |
| Bookmark | `domains/bookmark/context.md` | Tagging, Raindrop.io sync |
| Newsletter | `domains/newsletter/context.md` | LLM synthesis, scheduling |
| Spotlight | `domains/spotlight/context.md` | NLP intent, command execution |

---

## Workflows

For multi-step tasks, follow the defined workflows:

| Workflow | File | When to Use |
|----------|------|-------------|
| New Feature | `workflows/new-feature.md` | Adding new functionality |
| Bug Fix | `workflows/bug-fix.md` | Fixing issues |
| Release | `workflows/release.md` | Preparing deployments |

---

## Shared Knowledge

All agents MUST follow these shared guidelines:

| Document | Purpose |
|----------|---------|
| `shared/architecture.md` | CQRS, Hexagonal, data flow |
| `shared/conventions.md` | Naming, testing, git commits |
| `shared/boundaries.md` | ALWAYS/ASK/NEVER rules |
| `/docs/MARKETING-STRATEGY.md` | Pricing, communication roadmap, social templates (for `@marketer`) |

---

## Coordination Protocol

### 1. Single-Agent Tasks
```
User Request → Route to Primary Agent → Execute → Review (if needed)
```

### 2. Multi-Agent Tasks
```
User Request
    ↓
@engineer (orchestrates)
    ├── Defines contracts (DTOs, interfaces)
    ├── Delegates UI to @frontend
    ├── Delegates AI to @ai-specialist
    └── Requests review from @reviewer
```

### 3. Handoff Format
When handing off to another agent, include:
```markdown
## Handoff to @{agent}

### Context
- Task: [Brief description]
- Files involved: [List]
- Dependencies: [What must be done first]

### Your Scope
- [ ] Specific task 1
- [ ] Specific task 2

### Constraints
- [Any limitations or requirements]
```

---

## Escalation Rules

### Escalate to User
- Adding new dependencies (composer/npm)
- Changing database schema
- Modifying API contracts
- Infrastructure changes affecting production
- Architectural decisions with tradeoffs

### Escalate to @engineer (Lead)
- Cross-domain feature coordination
- Ambiguous requirements
- Performance concerns
- Breaking changes

### Escalate to @reviewer
- Security-sensitive code
- Complex business logic
- Public API changes

---

## Conflict Resolution

When agents disagree:
1. Document both perspectives
2. Reference `shared/architecture.md` for guidance
3. If unresolved, escalate to user with options

---

## Agent Capabilities Matrix

| Capability | Engineer | Frontend | AI Specialist | Infra | Reviewer | Marketer |
|------------|----------|----------|---------------|-------|----------|----------|
| Write PHP code | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| Write React code | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Write SQL/migrations | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| Modify Docker | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ |
| Design prompts | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Approve changes | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Create content | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |

---

## Quick Commands

```bash
# Find agent for a task
"Who handles [X]?" → Check Quick Reference table above

# Start a feature
"New feature: [description]" → Follow workflows/new-feature.md

# Fix a bug
"Bug: [description]" → Follow workflows/bug-fix.md

# Get domain context
"Working on [domain]" → Read domains/{domain}/context.md first
```
