# Workflow: Bug Fix

Use this workflow when fixing bugs in Signalist.

---

## Overview

```
1. REPRODUCE  → Confirm the bug exists
2. DIAGNOSE   → Find root cause
3. PLAN       → Determine fix approach
4. FIX        → Implement solution
5. TEST       → Add regression test
6. REVIEW     → Code review
7. MERGE      → Complete
```

---

## Step 1: Reproduce the Bug

### Gather Information
- [ ] What is the expected behavior?
- [ ] What is the actual behavior?
- [ ] Steps to reproduce
- [ ] Environment (browser, PHP version, etc.)
- [ ] Error messages or logs

### Reproduction Template
```markdown
## Bug Report

### Summary
[One line description]

### Expected
[What should happen]

### Actual
[What actually happens]

### Steps to Reproduce
1. [Step 1]
2. [Step 2]
3. [Step 3]

### Environment
- PHP: 8.5
- Browser: Chrome 120
- OS: macOS 14

### Logs/Errors
```
[Error message or stack trace]
```
```

### Confirm Reproduction
- [ ] Bug reproduced locally
- [ ] Bug is not a configuration issue
- [ ] Bug is not user error

---

## Step 2: Diagnose Root Cause

### Investigation Steps

1. **Check Logs**
   ```bash
   docker compose logs app | grep -i error
   tail -f var/log/dev.log
   ```

2. **Trace the Flow**
   - Start from the failing endpoint/component
   - Follow CQRS: Controller → Handler → Repository
   - Add temporary logging if needed

3. **Check Recent Changes**
   ```bash
   git log --oneline -20
   git diff HEAD~5
   ```

4. **Read Related Tests**
   - Are there existing tests that should catch this?
   - Why didn't they?

### Root Cause Template
```markdown
## Root Cause Analysis

### Bug
[Summary]

### Root Cause
[Why it happens]

### Location
- File: `src/Domain/X/Handler/XHandler.php`
- Line: 45
- Code: `$this->repo->find()` returns null when...

### Why Not Caught
- Missing test case for [scenario]
- Edge case not considered
```

---

## Step 3: Plan the Fix

### Fix Approaches

| Approach | Pros | Cons |
|----------|------|------|
| Quick fix | Fast | May not address root cause |
| Proper fix | Complete | Takes longer |
| Refactor | Prevents future bugs | Scope creep risk |

### Fix Plan Template
```markdown
## Fix Plan

### Approach
[Quick fix / Proper fix / Refactor]

### Changes
1. [File 1]: [Change description]
2. [File 2]: [Change description]

### New Tests
- [ ] Test for the exact scenario that caused the bug
- [ ] Test for related edge cases

### Risk Assessment
- Low/Medium/High risk
- Reason: [Why this risk level]
```

**If High Risk:** Get user approval before proceeding.

---

## Step 4: Implement the Fix

### Fix Checklist

- [ ] Change is minimal and focused
- [ ] No unrelated changes included
- [ ] Code follows existing patterns
- [ ] Error handling is appropriate

### Common Fix Patterns

**Null Check Missing**
```php
// Before (bug)
$result = $this->repo->find($id);
return $result->getName();

// After (fix)
$result = $this->repo->find($id);
if ($result === null) {
    throw new NotFoundException($id);
}
return $result->getName();
```

**Validation Missing**
```php
// Before (bug)
public function __construct(
    public string $url,
) {}

// After (fix)
public function __construct(
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url,
) {}
```

**Race Condition**
```php
// Before (bug)
if (!$this->repo->exists($id)) {
    $this->repo->create($entity); // Another request could create between check and create
}

// After (fix)
try {
    $this->repo->create($entity);
} catch (UniqueConstraintViolationException $e) {
    throw new AlreadyExistsException($id);
}
```

---

## Step 5: Add Regression Test

### Test Requirements

**Every bug fix MUST include a test that:**
1. Fails before the fix
2. Passes after the fix
3. Prevents regression

### Test Template
```php
/**
 * Regression test for bug: [description]
 * @see [Issue link if exists]
 */
public function testMethodName_BugScenario_CorrectBehavior(): void
{
    // Arrange: Set up the exact conditions that caused the bug

    // Act: Perform the action that triggered the bug

    // Assert: Verify correct behavior
}
```

### Run Tests
```bash
# Run specific test
./vendor/bin/phpunit tests/Unit/Domain/X/XHandlerTest.php --filter testMethodName

# Run all tests
make tests

# Verify the test fails without the fix
git stash
./vendor/bin/phpunit tests/Unit/Domain/X/XHandlerTest.php --filter testMethodName
# Should FAIL
git stash pop
./vendor/bin/phpunit tests/Unit/Domain/X/XHandlerTest.php --filter testMethodName
# Should PASS
```

---

## Step 6: Review

### Self-Review Checklist

- [ ] Fix addresses root cause, not just symptoms
- [ ] No unrelated changes
- [ ] Regression test added
- [ ] All existing tests pass
- [ ] Code follows conventions

### Hand Off to @reviewer
```markdown
## Bug Fix Review

### Bug
[Summary]

### Root Cause
[Brief explanation]

### Fix
[What was changed]

### Regression Test
`tests/Unit/Domain/X/XHandlerTest::testMethodName_BugScenario_CorrectBehavior`

### Files Changed
- `src/Domain/X/Handler/XHandler.php` (fix)
- `tests/Unit/Domain/X/XHandlerTest.php` (test)

### Testing Done
- [ ] Bug no longer reproduces
- [ ] New test passes
- [ ] All existing tests pass
```

---

## Step 7: Merge

### Commit Message
```
fix(domain): brief description of fix

Root cause: [explanation]
Regression test added.

Fixes #123 (if applicable)
```

### Pre-Merge Checklist
- [ ] Tests passing
- [ ] Lint passing
- [ ] Review approved
- [ ] No merge conflicts

---

## Hotfix (Production Bug)

For critical production bugs:

### Expedited Process
1. Create `hotfix/` branch from `main`
2. Minimal fix only
3. Expedited review
4. Merge to `main`
5. Deploy immediately
6. Create follow-up ticket for proper fix if needed

### Hotfix Commit
```
fix(domain): [HOTFIX] critical bug description

Emergency fix for production issue.
Follow-up: #456
```

---

## Post-Mortem (Optional)

For significant bugs, document lessons learned:

```markdown
## Post-Mortem: [Bug Summary]

### Impact
- Duration: X hours
- Users affected: Y

### Timeline
- [Time]: Bug reported
- [Time]: Root cause identified
- [Time]: Fix deployed

### Root Cause
[Detailed explanation]

### Prevention
- [ ] Add validation at [location]
- [ ] Add monitoring for [metric]
- [ ] Update documentation for [process]
```
