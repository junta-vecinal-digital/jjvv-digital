# AGENTS.md

This is a Software engineering student's project. It won't be a complete product, only an MVP. Read SPRINTS.md to see the current state.

## First Rules
- Do not install anything that is not necessary for the task.
- Do not run tests. Let the user test it.
- Generate only plain text for coding. Do not generate code files unless the user confirms it.
- Present a plan first, followed by a step-by-step guide with code.
- Treat REQUIREMENTS.md as the source of truth for RFU, RFS, and permissions.
- Any change to REQUIREMENTS.md requires the user's explicit confirmation for that change. Prior authorizations do not authorize future requirement changes.

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them - don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it - don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Define expected invalid inputs and ensure they are properly handled"
- "Fix the bug" → "Identify the failing case, apply the fix, and confirm it works"
- "Refactor X" → "Ensure functionality remains intact before and after changes"

For multi-step tasks, state a brief plan:
```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.
