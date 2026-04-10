# Watercooler Visual QA

Use this reference only when you need repo-specific capture guidance for Watercooler.

## Recommended Output Location

- Save screenshots under `tmp/visual-qa/`.

## Typical Capture Targets

- Home page:
  `http://localhost:4200/`
- Game room:
  `http://localhost:4200/game/<room-slug>`

## Suggested Wait Selectors

- Home page shell:
  `app-root`
- Game page shell:
  `app-game-page`
- Header cards:
  `.game-page__status-cards`
- Resource bank:
  `app-resource-bank`
- Visible market:
  `app-visible-market`
- Executive rail:
  `app-executive-row`

## Common Commands

Full-page game capture:

```powershell
node agent/skills/visual-qa-screenshot/scripts/capture-page.cjs --url http://localhost:4200/game/executive-meeting-visibility --output tmp/visual-qa/game-page.png --wait-for app-game-page --delay 1200
```

## Capture Loop

1. Apply UI changes.
2. Rebuild or restart the frontend if the containerized dev server is stale.
3. Capture the relevant route.
4. Inspect the image for spacing, clipping, hierarchy, and unused copy.
5. Repeat until the visual issue is resolved.
