# Frontend Plan

## Goals

The Angular frontend should present the Watercooler theme clearly while remaining a thin client over server-authoritative state.

## Primary Screens

1. Home page
2. Game pre-join page
3. Lobby
4. Main game board
5. Rules/help modal
6. Endgame/results modal

## Route Direction

- `/`: home page
- `/game/:slug`: game join, lobby, and gameplay container route

The game route can switch between sub-states based on server and local session state instead of introducing deep route complexity too early.

## Suggested Components

- `HomePageComponent`
- `GamePageComponent`
- `JoinPanelComponent`
- `AvatarBuilderComponent`
- `LobbyPanelComponent`
- `GameBoardComponent`
- `PlayerPanelComponent`
- `ResourceBankComponent`
- `CardMarketComponent`
- `ExecutiveRowComponent`
- `RulesDialogComponent`
- `EndgameDialogComponent`

## Client State Principles

- keep one centralized service for game/session state
- treat server payloads as source of truth
- send intents, not computed outcomes
- allow UI hints for likely legal actions, but never rely on them for authority

## UI Direction

- Angular Material as a base, but heavily themed
- retro intranet and faux enterprise portal styling
- readable information hierarchy over novelty
- humor through copy and framing, not obscured controls

## Frontend-Specific Risks

- connecting sockets before join acceptance
- scattering game state across many components
- making action affordances unclear in the name of style
- baking rules assumptions into the UI instead of shared state contracts
