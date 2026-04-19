#!/usr/bin/env python3

from __future__ import annotations

import json
import os
import re
import subprocess
import sys
from pathlib import Path


REPO_ROOT = Path(__file__).resolve().parents[2]
PROTECTED_BRANCHES = {"main", "master", "develop"}
INSTRUCTION_FILES = {"AGENTS.md", "CLAUDE.md"}
INSTRUCTION_PREFIX = ".claude/"
BACKEND_PREFIXES = ("src/", "config/", "tests/")
FRONTEND_PREFIXES = ("frontend/",)
ASYNCHRONOUS_ENTRYPOINT_PREFIXES = (
    "src/UI/Controller/",
    "src/Infrastructure/ApiPlatform/State/",
)
SENSITIVE_SURFACE_PREFIXES = (
    "src/Domain/Auth/",
    "src/Infrastructure/Auth/",
    "src/Infrastructure/AI/",
    "src/Infrastructure/MCP/",
    "config/packages/security",
    "config/routes",
)
ENV_FILE_PATTERN = re.compile(r"(^|/)\.env(\..+)?$")


def load_tool_input() -> dict:
    raw = os.environ.get("CLAUDE_TOOL_INPUT", "{}")
    try:
        return json.loads(raw) if raw else {}
    except json.JSONDecodeError:
        return {}


def repo_path(path: str) -> str:
    try:
        resolved = Path(path).resolve()
        return resolved.relative_to(REPO_ROOT).as_posix()
    except Exception:
        return path.replace("\\", "/")


def collect_paths(value: object) -> list[str]:
    paths: list[str] = []

    if isinstance(value, str):
        normalized = value.replace("\\", "/")
        if "/" in normalized or normalized.startswith("."):
            paths.append(repo_path(normalized))
        return paths

    if isinstance(value, list):
        for item in value:
            paths.extend(collect_paths(item))
        return paths

    if isinstance(value, dict):
        for key, item in value.items():
            if key in {"path", "file_path", "target_file", "paths", "files"}:
                paths.extend(collect_paths(item))
        return paths

    return paths


def current_branch() -> str | None:
    try:
        return (
            subprocess.check_output(
                ["git", "rev-parse", "--abbrev-ref", "HEAD"],
                cwd=REPO_ROOT,
                stderr=subprocess.DEVNULL,
                text=True,
            )
            .strip()
        )
    except Exception:
        return None


def git_changed_files(*args: str) -> list[str]:
    try:
        output = subprocess.check_output(
            ["git", *args],
            cwd=REPO_ROOT,
            stderr=subprocess.DEVNULL,
            text=True,
        )
        return [line.strip() for line in output.splitlines() if line.strip()]
    except Exception:
        return []


def all_changed_files() -> list[str]:
    files = set(git_changed_files("diff", "--name-only"))
    files.update(git_changed_files("diff", "--cached", "--name-only"))
    return sorted(files)


def is_instruction_file(path: str) -> bool:
    return path in INSTRUCTION_FILES or path.startswith(INSTRUCTION_PREFIX)


def is_env_file(path: str) -> bool:
    return bool(ENV_FILE_PATTERN.search(path))


def touches_backend(path: str) -> bool:
    return path.startswith(BACKEND_PREFIXES)


def touches_frontend(path: str) -> bool:
    return path.startswith(FRONTEND_PREFIXES)


def touches_async_entrypoint(path: str) -> bool:
    return path.startswith(ASYNCHRONOUS_ENTRYPOINT_PREFIXES)


def touches_sensitive_surface(path: str) -> bool:
    return path.startswith(SENSITIVE_SURFACE_PREFIXES)


def emit_unique(warnings: list[str]) -> int:
    seen: set[str] = set()
    for warning in warnings:
        if warning in seen:
            continue
        seen.add(warning)
        print(f"[Signalist guardrail] {warning}", file=sys.stderr)
    return 0


def handle_bash(data: dict) -> int:
    command = str(data.get("command", "")).strip()
    warnings: list[str] = []

    if re.search(r"\bgit\s+(commit|push)\b", command):
        branch = current_branch()
        if branch in PROTECTED_BRANCHES:
            warnings.append(
                f"Current branch is `{branch}`. Shared branches are protected by project policy; prefer a dedicated feature, fix, or hotfix branch before commit/push."
            )

    if re.search(r"\bgit\s+add\b", command):
        staged_files = git_changed_files("diff", "--cached", "--name-only")

        if any(is_instruction_file(path) for path in staged_files):
            warnings.append(
                "Instruction files are staged. Changes to `AGENTS.md`, `CLAUDE.md`, or `.claude/*` require explicit confirmation; keep `CLAUDE.md` as a thin pointer."
            )

        if "phpstan.neon" in staged_files:
            warnings.append(
                "`phpstan.neon` is staged. PHPStan config changes are exceptional; prefer fixing code, types, or PHPDoc first and justify any config relaxation before commit preparation."
            )

        if any(is_env_file(path) for path in staged_files):
            warnings.append(
                "Sensitive env files are staged. Do not expose, stage casually, or commit `.env*` files unless there is an explicit and justified need."
            )

        if any(touches_backend(path) for path in staged_files) and any(
            touches_frontend(path) for path in staged_files
        ):
            warnings.append(
                "Both backend and frontend files are staged. Verify both surfaces: `make quality`, `make tests`, `make tests-api`, `make front-lint`, `make front-test`, and `npm run typecheck` in `frontend/`."
            )

        if any(touches_async_entrypoint(path) for path in staged_files):
            warnings.append(
                "Synchronous entrypoint files are staged. If this change adds outbound HTTP, RSS, or AI work in the request cycle, prefer Messenger/message-handler patterns for slow side effects."
            )

        if any(touches_sensitive_surface(path) for path in staged_files):
            warnings.append(
                "Auth, AI, MCP, or other sensitive surfaces are staged. Run a security/privacy review and ensure negative-path coverage before commit preparation."
            )

    return emit_unique(warnings)


def handle_file_tool(data: dict, mode: str) -> int:
    warnings: list[str] = []
    paths = collect_paths(data)
    changed_files = all_changed_files()

    if any(is_instruction_file(path) for path in paths):
        warnings.append(
            "Instruction files are being changed. This repository requires explicit confirmation for instruction updates; keep `CLAUDE.md` as a thin pointer and avoid duplicated guidance."
        )

    if any(path == "phpstan.neon" for path in paths):
        warnings.append(
            "`phpstan.neon` is being changed. Prefer fixing PHPStan issues in code, types, or PHPDoc before touching static-analysis configuration."
        )

    if any(is_env_file(path) for path in paths):
        warnings.append(
            "Sensitive `.env*` files are in scope. Do not expose them in prompts, logs, comments, or commits."
        )

    if any(touches_async_entrypoint(path) for path in paths):
        warnings.append(
            "This change touches synchronous entrypoints. Keep slow outbound RSS, AI, or HTTP side effects behind explicit async boundaries when applicable."
        )

    if any(touches_sensitive_surface(path) for path in paths):
        warnings.append(
            "This change touches auth, AI, MCP, or other sensitive surfaces. Security/privacy review and negative-path testing are expected."
        )

    if mode in {"edit", "write"} and any(touches_backend(path) for path in changed_files) and any(
        touches_frontend(path) for path in changed_files
    ):
        warnings.append(
            "Current worktree spans backend and frontend changes. Remember to verify both stacks before preparing a commit."
        )

    return emit_unique(warnings)


def main() -> int:
    mode = sys.argv[1] if len(sys.argv) > 1 else ""
    data = load_tool_input()

    if mode == "bash":
        return handle_bash(data)
    if mode in {"edit", "write", "read"}:
        return handle_file_tool(data, mode)

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
