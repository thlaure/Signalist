# Workflow: New Feature Implementation

Use this workflow when adding new functionality to Signalist.

---

## Overview

```
1. UNDERSTAND  → Clarify requirements
2. EXPLORE     → Map affected domains and files
3. PLAN        → Design implementation approach
4. APPROVE     → Get user sign-off
5. IMPLEMENT   → Build incrementally
6. TEST        → Write and run tests
7. REVIEW      → Code review
8. MERGE       → Complete
```

---

## Step 1: Understand Requirements

### Questions to Answer
- [ ] What problem does this feature solve?
- [ ] Who is the user persona?
- [ ] What are the acceptance criteria?
- [ ] What are the edge cases?
- [ ] Are there any constraints (performance, security)?

### If Unclear
Use this template to ask the user:
```markdown
## Clarification Needed

### Feature: [Name]

### My Understanding
[What I think the feature should do]

### Questions
1. [Specific question]
2. [Specific question]

### Assumptions (please confirm)
- [Assumption 1]
- [Assumption 2]
```

---

## Step 2: Explore Context

### Read Domain Context
```
.ai-agents/domains/{relevant-domain}/context.md
```

### Check Existing Code
- [ ] Related entities in `src/Entity/`
- [ ] Similar handlers in `src/Domain/`
- [ ] Existing tests for patterns
- [ ] API conventions in existing controllers

### Identify Affected Areas
| Area | Impact |
|------|--------|
| Domain(s) | Which domains are touched |
| Entities | New or modified |
| API | New endpoints |
| Frontend | New components |
| AI | LLM integration needed? |
| Infra | Schema changes? |

---

## Step 3: Plan Implementation

### Create Implementation Plan
```markdown
## Implementation Plan: [Feature Name]

### Scope
[1-2 sentence description]

### Components

#### 1. Backend
- [ ] `CreateXCommand` - [purpose]
- [ ] `CreateXHandler` - [purpose]
- [ ] `CreateXInput` - [validation]
- [ ] `CreateXController` - [route]

#### 2. Frontend (if applicable)
- [ ] `XComponent` - [purpose]
- [ ] `useX` hook - [purpose]

#### 3. Tests
- [ ] `CreateXHandlerTest`
- [ ] `CreateXControllerTest`

### Files to Create
- `src/Domain/X/Command/CreateXCommand.php`
- `src/Domain/X/Handler/CreateXHandler.php`
- ...

### Files to Modify
- `src/Entity/X.php` - Add field Y
- ...

### Dependencies
- Requires: [other task/feature]
- Blocks: [nothing/other task]

### Risks
- [Potential issue and mitigation]
```

---

## Step 4: Get Approval

Present plan to user with:
```markdown
## Ready for Approval

### Feature
[Name]

### Summary
[What will be built]

### Changes
- X new files
- Y modified files
- Z new API endpoints

### Questions Before Proceeding
1. [Any remaining uncertainties]

**Proceed with implementation?**
```

**Wait for explicit approval before coding.**

---

## Step 5: Implement

### Order of Implementation

1. **Entity/Model** (if new)
   - Create entity
   - Create migration
   - Verify with `make migrate-diff`

2. **Port (Interface)**
   - Define repository interface in `src/Domain/{X}/Port/`

3. **Infrastructure**
   - Implement repository in `src/Infrastructure/Persistence/`

4. **Command/Query**
   - Create in `src/Domain/{X}/Command/` or `Query/`

5. **Handler**
   - Create in `src/Domain/{X}/Handler/`
   - This is where business logic goes

6. **DTOs**
   - InputDTO with validation
   - OutputDTO for response

7. **Controller**
   - Create in `src/UI/Controller/{X}/`
   - Orchestration only

8. **Frontend** (if applicable)
   - Hand off to @frontend

### Incremental Commits
```bash
feat(x): add CreateX command and handler
feat(x): add CreateX controller and endpoint
test(x): add CreateXHandler unit tests
```

---

## Step 6: Test

### Required Tests

| Type | Location | Coverage |
|------|----------|----------|
| Unit | `tests/Unit/Domain/{X}/Handler/` | Handler logic |
| Web | `tests/Web/Controller/{X}/` | HTTP flow |
| Integration | `tests/Integration/` | DB interactions |

### Test Naming
```
test{Method}_{Scenario}_{Expected}
```

### Run Tests
```bash
make tests          # All tests
make tests-unit     # Unit only
make analyse        # Static analysis
make lint           # Code style
```

---

## Step 7: Review

### Hand Off to @reviewer
```markdown
## Ready for Review

### Feature
[Name]

### PR/Changes
- [List of files]

### Test Coverage
- Unit: X tests
- Web: Y tests
- Coverage: Z%

### Review Focus
- [ ] CQRS compliance
- [ ] Error handling
- [ ] Input validation
- [ ] Test coverage
```

### Address Feedback
- Fix issues raised
- Run tests again
- Request re-review

---

## Step 8: Merge

### Pre-Merge Checklist
- [ ] All tests passing
- [ ] Lint passing
- [ ] Static analysis passing
- [ ] Code review approved
- [ ] No merge conflicts
- [ ] Commit messages follow conventions

### Merge
```bash
git checkout main
git pull
git merge feature/x --no-ff
git push
```

---

## Multi-Agent Coordination

If feature spans multiple agents:

### Coordination Template
```markdown
## Feature: [Name]

### @engineer
- [ ] Backend API
- [ ] Domain logic

### @frontend
- [ ] UI components
- [ ] API integration

### @ai-specialist (if needed)
- [ ] LLM integration
- [ ] Embeddings

### @infra (if needed)
- [ ] Schema migration
- [ ] Index optimization

### Handoff Order
1. @engineer creates API contract
2. @frontend builds UI in parallel
3. @ai-specialist integrates AI features
4. @infra optimizes if needed
5. @reviewer reviews all changes
```

---

## Rollback Plan

If something goes wrong:

1. Revert merge commit
2. Fix issue on feature branch
3. Re-test
4. Re-merge
