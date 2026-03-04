Start an expedited hotfix workflow for the production issue: $ARGUMENTS

This is a HOTFIX — keep changes minimal and focused on the fix only.

## Steps

1. **Create hotfix branch from master**
   ```bash
   git checkout master && git pull
   git checkout -b hotfix/$ARGUMENTS
   ```

2. **Diagnose quickly**
   - Check logs: `docker compose logs app | grep -i error`
   - Identify the exact file and line causing the issue
   - Root cause in one sentence

3. **Minimal fix only**
   - Change the least amount of code necessary
   - No refactoring, no unrelated improvements
   - If the root cause requires a larger fix, implement a safe temporary workaround and create a follow-up task

4. **Regression test**
   - Write one test that reproduces the bug and passes after the fix
   - Run `make tests-unit`

5. **Commit**
   ```
   fix(domain): [HOTFIX] brief description

   Root cause: [one line]
   Regression test added.
   ```

6. **PR and deploy**
   - `gh pr create` targeting `master`
   - After merge, monitor logs for 10 minutes

Read `.claude/workflows/bug-fix.md` for the full workflow if the issue requires deeper investigation.
