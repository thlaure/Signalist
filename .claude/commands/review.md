Perform a code review of the current changes. Context: $ARGUMENTS

If no context is given, run `git diff HEAD` and `git diff --staged` to get changes.

Review against these criteria:

**Architecture**
- [ ] CQRS respected: no business logic in controllers, handlers only orchestrate
- [ ] No coupling between domains
- [ ] Interfaces used for dependencies (not concrete classes)
- [ ] Async for RSS/AI operations

**Code Quality**
- [ ] `declare(strict_types=1)` present
- [ ] No `mixed` or untyped arrays without docblock
- [ ] Meaningful names (no `FeedService`, `FeedManager`, etc.)
- [ ] PHP CS Fixer camelCase test names (no underscores)

**Security**
- [ ] No hardcoded secrets
- [ ] Input validated via InputDTO before reaching handler
- [ ] No raw personal data sent to external AI services

**Testing**
- [ ] Unit test for every new handler
- [ ] Behat scenario for every new API endpoint
- [ ] Test naming: `testInvokeWithValidDataReturnsFeedId()` (camelCase)

**Frontend (if applicable)**
- [ ] No `any` type
- [ ] All strings use `t('key')` from react-i18next
- [ ] Keys added to both `en.json` and `fr.json`

Report findings as: **Critical** (must fix) / **High** (should fix) / **Low** (suggestion).
