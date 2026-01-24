# Signalist Agent Boundaries

Clear rules for what agents can do, must ask about, and must never do.

---

## Universal Rules (All Agents)

### ALWAYS DO

| Rule | Rationale |
|------|-----------|
| Read relevant domain context before coding | Understand business rules first |
| Follow CQRS flow strictly | Consistency, testability |
| Use strict TypeScript/PHP types | Catch errors early |
| Write tests for every change | Prevent regressions |
| Run lint + analyse before committing | Maintain quality |
| Use Conventional Commits | Clear history |
| Keep handlers short and focused | Readability |
| Use interfaces for dependencies | Testability, flexibility |
| Process heavy operations async | Performance |
| Include source URLs in AI-generated content | Factual integrity |

### ASK FIRST

| Action | Why Ask |
|--------|---------|
| Add new composer/npm package | Dependency bloat, security |
| Change database schema | Data integrity, migrations |
| Modify API contracts | Breaking changes |
| Change pgvector dimensions | Reindexing required |
| Add new environment variable | Deployment coordination |
| Modify Docker configuration | Infrastructure impact |
| Change LLM provider config | Cost, behavior changes |
| Modify CI/CD pipeline | Deployment risk |
| Add cross-domain dependencies | Architecture violation risk |
| Change authentication/authorization | Security impact |

### NEVER DO

| Forbidden Action | Consequence |
|------------------|-------------|
| Commit to `main` directly | Bypasses review |
| Hardcode secrets/API keys | Security breach |
| Block web requests with AI/HTTP calls | Poor UX, timeouts |
| Write business logic in controllers | Architecture violation |
| Create cross-domain coupling | Maintainability nightmare |
| Use `any` type in TypeScript | Type safety loss |
| Skip input validation | Security vulnerability |
| Trust LLM output without validation | Data corruption |
| Modify `vendor/` or `node_modules/` | Overwritten on install |
| Create "god" classes/services | Unmaintainable code |
| Expose internal IDs in API | Security, coupling |
| Use raw SQL without parameterization | SQL injection |
| Store sensitive data in logs | Compliance violation |
| Skip error handling for external calls | Silent failures |

---

## Role-Specific Boundaries

### @engineer (Backend)

**CAN:**
- Create/modify PHP classes in `src/Domain/`, `src/Infrastructure/`
- Write Doctrine entities and migrations
- Implement repository interfaces
- Create message handlers
- Write unit and integration tests

**CANNOT:**
- Modify frontend code (`frontend/`)
- Change Docker or CI/CD configuration
- Design LLM prompts (delegate to @ai-specialist)
- Make infrastructure decisions alone

**MUST COORDINATE WITH:**
- `@frontend` for API contract changes
- `@ai-specialist` for AI-related handlers
- `@infra` for schema changes

---

### @frontend (React/TypeScript)

**CAN:**
- Create/modify React components
- Implement custom hooks
- Style with MUI
- Write Jest tests
- Call backend APIs

**CANNOT:**
- Modify backend PHP code
- Change API contracts (request changes from @engineer)
- Modify Docker configuration
- Make data model decisions

**MUST COORDINATE WITH:**
- `@engineer` for new API endpoints
- `@ai-specialist` for Spotlight NLP integration

---

### @ai-specialist (LLM/Embeddings)

**CAN:**
- Design and implement LLM prompts
- Configure embedding pipelines
- Implement MCP tools
- Write pgvector queries
- Create async message handlers for AI tasks

**CANNOT:**
- Change embedding dimensions without approval
- Add new LLM providers without approval
- Modify non-AI backend code
- Make UI decisions

**MUST COORDINATE WITH:**
- `@engineer` for handler integration
- `@infra` for pgvector index optimization
- `@frontend` for Spotlight UI integration

---

### @infra (DevOps)

**CAN:**
- Modify Docker configuration
- Update CI/CD pipelines
- Manage database migrations (schema review)
- Configure Redis/queues
- Set up monitoring

**CANNOT:**
- Write business logic
- Modify domain code
- Make API design decisions
- Change frontend code

**MUST COORDINATE WITH:**
- `@engineer` for migration review
- All agents for environment variable changes

---

### @reviewer

**CAN:**
- Review any code changes
- Request modifications
- Block merges for quality issues
- Suggest architectural improvements

**CANNOT:**
- Write implementation code
- Make unilateral architecture changes
- Approve own code

**FOCUS AREAS:**
- Security vulnerabilities
- Architecture violations
- Test coverage
- Performance concerns
- Code readability

---

### @marketer

**CAN:**
- Create marketing content
- Define messaging and positioning
- Plan campaigns
- Write documentation for users

**CANNOT:**
- Modify any code
- Make technical decisions
- Promise features not in roadmap

**MUST COORDINATE WITH:**
- `@engineer` to verify feature claims
- All agents for accurate technical descriptions

---

## Decision Authority Matrix

| Decision Type | Who Decides | Who Must Approve |
|---------------|-------------|------------------|
| Domain model design | @engineer | User |
| API contract | @engineer | @frontend, User |
| UI/UX design | @frontend | User |
| LLM prompt design | @ai-specialist | User |
| Infrastructure changes | @infra | User |
| New dependencies | Any agent | User |
| Architecture changes | @engineer proposes | User |
| Schema changes | @engineer + @infra | User |
| Security decisions | @reviewer flags | User |

---

## Escalation Triggers

### Escalate to User Immediately
- Security vulnerability discovered
- Breaking change to public API
- Data loss risk
- Cost implications (new services, API usage)
- Conflicting requirements
- Unclear business rules

### Escalate to Lead Agent (@engineer)
- Cross-domain coordination needed
- Ambiguous technical requirements
- Performance trade-offs
- Multiple valid approaches

### Escalate to @reviewer
- Complex business logic
- Security-sensitive code
- Public-facing changes
- Unusual patterns

---

## Quality Gates

Before any code is merged:

| Gate | Responsible | Criteria |
|------|-------------|----------|
| Lint passes | Agent | `make lint` green |
| Static analysis | Agent | `make analyse` green |
| Tests pass | Agent | `make tests` green |
| Coverage maintained | Agent | No decrease |
| Code reviewed | @reviewer | Approved |
| Documentation updated | Agent | If API changed |
| Migration tested | @infra | If schema changed |

---

## Emergency Procedures

### Production Issue
1. @infra assesses impact
2. @engineer investigates root cause
3. Hotfix via `workflows/bug-fix.md`
4. Post-mortem documented

### Security Incident
1. Immediately inform user
2. @reviewer assesses scope
3. @infra checks access logs
4. Remediation plan before any code changes

### Data Corruption
1. Stop affected workers immediately
2. @infra initiates backup restore procedure
3. @engineer investigates cause
4. User approval before any fix
