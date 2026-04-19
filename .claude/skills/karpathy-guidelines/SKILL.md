---
name: karpathy-guidelines
description: Use for bug fixes, refactors, reviews, or ambiguity-heavy implementation tasks to reinforce explicit assumptions, minimal diffs, simplicity, and direct verification.
license: MIT
---

# Karpathy Guidelines

Use this skill when the task risks overcomplication, silent assumptions, or broad changes that are not tightly tied to the request.

This skill reinforces the repository rules in `AGENTS.md`; it does not replace them.

## Apply This Skill When

- the request is ambiguous and multiple interpretations are plausible
- the change could easily grow into unnecessary abstraction or speculative flexibility
- the task is a bug fix, refactor, or code review where disciplined scope control matters
- you need to keep the diff tightly coupled to the requested outcome

## What To Emphasize

### 1. Think Before Coding

- state assumptions that materially affect the implementation
- surface ambiguity instead of choosing silently
- if a simpler approach exists, say so
- ask when uncertainty is risky enough to produce the wrong change

### 2. Simplicity First

- implement the minimum code that solves the requested problem
- avoid speculative abstractions, configurability, and future-proofing that were not requested
- prefer the version a senior engineer would describe as clear and unsurprising

### 3. Surgical Changes

- touch only what the request and its verification require
- do not refactor adjacent code unless it is necessary for correctness
- clean up only the imports, variables, dead code, or formatting made obsolete by your own change

### 4. Goal-Driven Execution

- define success in verifiable terms before implementing non-trivial changes
- bug fix: reproduce first when practical, then verify the fix
- behavior change: add or update tests that prove the requested outcome
- refactor: verify behavior before and after with the relevant test suite

## Expected Outcome

- fewer unnecessary lines in diffs
- clearer assumptions and tradeoffs before implementation
- simpler code with less speculative structure
- verification that directly proves the request was satisfied
