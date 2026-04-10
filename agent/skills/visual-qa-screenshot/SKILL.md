---
name: visual-qa-screenshot
description: Capture repeatable browser screenshots for UI review with an npm-driven Playwright workflow. Use when Codex needs to inspect layout, spacing, responsive behavior, or visual regressions in Watercooler by opening a local route, waiting for the right UI state, and saving a screenshot artifact.
---

# Visual QA Screenshot

Use this skill to produce reproducible UI screenshots instead of relying on ad hoc browser captures.

## Workflow

1. Make sure the target app is running.
2. Choose the route to capture and the selector that proves the page is ready.
3. Run `scripts/capture-page.cjs` through Node so it shells out to `npm exec` with Playwright.
4. Review the saved image and report any visual issues with file references.

## Command

Run:

```powershell
node agent/skills/visual-qa-screenshot/scripts/capture-page.cjs --url http://localhost:4200/ --output tmp/visual-qa/home.png --wait-for body
```

Useful flags:

- `--url <url>` required target page
- `--output <path>` required image path
- `--wait-for <selector>` wait for a stable element before capture
- `--delay <ms>` add settling time for fonts, animations, or async UI
- `--viewport <width>x<height>` override the default `1440x1400`
- `--full-page false` limit the capture to the initial viewport

## Watercooler Notes

- Prefer saving artifacts under `tmp/visual-qa/`.
- Rebuild or restart the frontend before capture if recent UI edits are not visible yet.
- Use page-level selectors such as `app-game-page` or stable board/card selectors instead of brittle text-only waits.

Load `references/watercooler-visual-qa.md` when you need route examples, selector suggestions, or the recommended capture loop for this repo.
