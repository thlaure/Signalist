Run the full quality check suite and report results.

Execute in order:
1. `make lint` — PHP CS Fixer
2. `make analyse` — PHPStan level 9
3. `make rector` — Rector refactoring check
4. `make tests-unit` — PHPUnit unit tests

For each step that fails, explain:
- What failed and why
- The exact fix needed
- Whether it can be auto-fixed (e.g. `make lint` can auto-fix CS issues)

Context (optional): $ARGUMENTS
