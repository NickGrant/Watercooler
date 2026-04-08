# Watercooler

Watercooler is a planned browser-based multiplayer board game inspired by Splendor and reskinned as an absurd corporate satire. Players join a game by URL, build a tiny office-worker avatar, collect office resources, acquire Workplace Advantages, attract Executives, and race for Office Prestige.

This repository is currently in a documentation-first planning stage. We are not yet implementing the application described in `resources/INITIAL_PROMPT.md`.

## Project Intent

The planned MVP will use:

- Angular for the frontend
- PHP for the HTTP API
- PHP for a separate WebSocket service
- MySQL for persistence
- Angular Material as a base UI layer with a retro intranet skin

Core product goals:

- preserve Splendor-style gameplay structure
- support realtime multiplayer through shared game URLs
- keep the server authoritative for gameplay state
- deliver a readable, funny office-satire presentation

## Repository Structure

- `AGENTS.md`: guidance for coding agents working in this repo
- `resources/INITIAL_PROMPT.md`: original project brief
- `resources/planning/`: split planning docs for targeted context

## Planning Docs

Use `resources/planning/index.md` as the entry point for detailed planning. The planning docs are intentionally broken into smaller files so future implementation work can load only the context it needs.

Current planning areas include:

- architecture
- gameplay rules and thematic mapping
- frontend structure
- backend API design
- realtime service design
- data model and persistence
- lobby and session flow
- content, theme, and avatar direction
- phased roadmap

## Current Status

This repo currently contains documentation only.

Immediate non-code goals completed here:

- create a human-readable project overview
- create an agent-readable operating guide
- split the initial prompt into focused planning docs
- initialize version control for the project

## Next Phase

When implementation begins, the expected first wave of work is:

1. scaffold the Angular frontend, PHP API, PHP WebSocket service, and project structure
2. define MySQL schema and migrations
3. implement game creation, slug generation, and join flow
4. establish lobby flow and server-authoritative realtime messaging

## Notes

- Gameplay should remain mechanically faithful to Splendor.
- Theme changes should improve clarity and flavor, not alter the core strategy loop.
- The project is session-based and should avoid account-system complexity.
