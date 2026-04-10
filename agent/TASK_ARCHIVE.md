# TASK_ARCHIVE.md

## Purpose

This file stores completed work so `agent/TASKS.md` can stay small and useful.

## Completed Tasks

### TASK-065

- Name: Build reusable resource icon component
- Priority: High
- Blocked By: None
- Blocks: TASK-066, TASK-068
- Description: Create a shared resource icon component with regular and small variants using square icon art and a superscript value badge with a solid background so resource display logic is centralized before further board-density cleanup.

### TASK-055

- Name: Implement true websocket gameplay transport
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Replace the current scaffolded/polling-based active-game refresh path with a real websocket transport that pushes authoritative gameplay state changes to every connected client.

### TASK-066

- Name: Tighten project cards and increase per-row density
- Priority: High
- Blocked By: TASK-065
- Blocks: None
- Description: Make project cards narrower and denser so more backlog items can fit on a single row without sacrificing readability.

### TASK-067

- Name: Remove border from take-selected-resources button
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Update the take-selected-resources control styling so it matches the other primary actions instead of reading as a bordered special case.

### TASK-068

- Name: Split resource bank into resources and Executive Favor columns
- Priority: High
- Blocked By: TASK-065
- Blocks: None
- Description: Rework the resource-bank layout so the main resource acquisition flow lives in one column while Executive Favor is separated into its own clearer column.

### TASK-069

- Name: Redesign executive cards from mock reference
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Rebuild the executive card presentation to match the structural intent of `resources/ui-mocks/executive-card-v1/mock.png`, using the portrait as the dominant visual, a bordered/stylistic portrait background treatment, a distinct executive-name band, a separate prestige number block, and requirement icons that visually indicate when their thresholds are satisfied.

### TASK-063

- Name: Rename projects to be more expressive
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Rewrite the seeded project names so backlog and completed-project content feels more expressive and thematic during play without changing gameplay balance.

### TASK-064

- Name: Create screenshot skill for game-screen visual capture
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Create a shared agent skill that can trigger a game-screen screenshot through an npm-driven Playwright workflow so UI reviews can be repeated consistently during UAT.

### TASK-054

- Name: Tighten game-page layout and contextual help
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Reduce below-the-fold scrolling on the main game route, remove passive instructional copy from active play, and replace it with tighter layout plus contextual tooltip help.

### TASK-053

- Name: Stabilize active-game state sync and change alerts
- Priority: High
- Blocked By: None
- Blocks: TASK-055
- Description: Ensure the frontend keeps the active game route synchronized with authoritative state, clearly reflects turn ownership and state changes, and surfaces short bottom-of-screen toast notifications when important board changes occur.

### TASK-052

- Name: Expand the game scene to use more of the available viewport
- Priority: High
- Blocked By: TASK-038
- Blocks: None
- Description: Adjust the game screen layout so it uses more of the available horizontal space and brings more of the primary board state above the fold.

### TASK-039

- Name: Resolve validated UAT issues
- Priority: High
- Blocked By: TASK-038
- Blocks: None
- Description: Implement, test, and verify the fixes for confirmed UAT findings once they have been reproduced and scoped.

### TASK-038

- Name: Capture and triage active UAT findings
- Priority: High
- Blocked By: None
- Blocks: TASK-039, TASK-046
- Description: Record incoming UAT issues, confirm expected behavior, reproduce defects, and turn validated findings into implementation tasks.

### TASK-051

- Name: Redesign executive cards with a Guess Who style visual treatment
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Redesign the executive cards to feel more visual and character-forward, with a presentation reminiscent of Guess Who style portrait cards.

### TASK-050

- Name: Replace resource text labels with compact iconography
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Replace or supplement verbose resource labels with recognizable iconography so the resource displays become significantly tighter and faster to scan.

### TASK-047

- Name: Auto-update the waiting room roster
- Priority: High
- Blocked By: TASK-038
- Blocks: None
- Description: Make the waiting room player list update automatically so new joins, reconnects, and status changes appear without a manual refresh during UAT.

### TASK-049

- Name: Add padding to the current room state box
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Add internal spacing to the current room state status box so it feels visually balanced with the rest of the screen.

### TASK-048

- Name: Format room slugs as readable title text
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Present the room name in the page title area with spaces instead of hyphens and with each word capitalized so it reads like a real room name.

### TASK-044

- Name: Rework avatar picker order and interaction model
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Change the avatar builder to present options in hair, face, body order and replace the current selectors with image carousel controls that preview each choice clearly.

### TASK-045

- Name: Create avatar option art assets
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Produce or integrate the graphic assets needed to represent each avatar choice in the carousel-based builder.

### TASK-043

- Name: Remove development-oriented copy from the game screen
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Remove placeholder or development-facing text from the game route, including sections like "Authenticated Game State", so the UI reads like a finished product during UAT.

### TASK-042

- Name: Add consistent padding to game screen columns
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Tighten the game board layout so each major column and panel has consistent internal spacing and does not feel cramped during gameplay UAT.

### TASK-041

- Name: Widen landing page h1 treatment
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Adjust the landing page heading layout so the primary h1 has more horizontal room and reads cleanly during UAT review.

### TASK-040

- Name: Remove home page status-grid section
- Priority: Medium
- Blocked By: TASK-038
- Blocks: None
- Description: Remove the status-grid section from the home page so the landing experience stays focused on the primary create-game path during UAT cleanup.

### TASK-046

- Name: Debug create-new-game failure
- Priority: High
- Blocked By: TASK-038
- Blocks: None
- Description: Reproduce and diagnose why the create-new-game flow is failing during UAT, then identify whether the issue is in the frontend request path, API behavior, Docker environment, or persisted data state.

### TASK-031

- Name: Build in-app rules and help surface
- Priority: Medium
- Blocked By: TASK-027
- Blocks: None
- Description: Provide a concise rules modal or help surface using Watercooler terminology while remaining mechanically precise.

### TASK-032

- Name: Apply retro intranet visual skin
- Priority: Medium
- Blocked By: TASK-027
- Blocks: None
- Description: Theme the Angular Material base into a readable faux-corporate portal with strong visual identity.

### TASK-029

- Name: Build endgame and results presentation
- Priority: Medium
- Blocked By: TASK-026, TASK-027
- Blocks: None
- Description: Show the winner, final standings, and tie-break explanation with options to leave or start a new session.

### TASK-027

- Name: Build main game board UI
- Priority: High
- Blocked By: None
- Blocks: TASK-031, TASK-032
- Description: Render the market, bank, executives, player panels, turn state, and action surfaces in a readable real-time layout.
### TASK-034

- Name: Seed starter Workplace Advantages and Executives content
- Priority: Medium
- Blocked By: None
- Blocks: TASK-031
- Description: Create the initial themed content set for cards, executives, labels, and system copy.

### TASK-028

- Name: Implement reconnect and resync flow
- Priority: Medium
- Blocked By: None
- Blocks: TASK-033
- Description: Allow a player with a valid temporary session to re-enter an in-progress or lobby game without a full account system.

### TASK-033

- Name: Add disconnect, stale-action, and recovery handling
- Priority: Medium
- Blocked By: TASK-028
- Blocks: None
- Description: Handle socket failures, stale client actions, and recovery messaging gracefully across lobby and game states.

### TASK-030

- Name: Add core game rules automated tests
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Cover legal and illegal move validation, executive logic, and endgame behavior at the rules layer.

### TASK-026

- Name: Implement endgame trigger and winner resolution
- Priority: High
- Blocked By: None
- Blocks: TASK-029, TASK-030
- Description: Detect the prestige threshold, complete the round correctly, and resolve the winner according to Splendor-equivalent rules.

### TASK-025

- Name: Implement Executive award and Office Prestige logic
- Priority: High
- Blocked By: None
- Blocks: TASK-026, TASK-030
- Description: Award executives automatically when requirements are met and maintain correct prestige totals.

### TASK-024

- Name: Implement Workplace Advantage purchase flow
- Priority: High
- Blocked By: None
- Blocks: TASK-025, TASK-026, TASK-030
- Description: Add affordability validation, permanent discount application, cost payment, and purchased-card state updates.

### TASK-023

- Name: Implement project-claiming and Executive Favor flow
- Priority: High
- Blocked By: None
- Blocks: TASK-026, TASK-030
- Description: Add reserve-equivalent behavior, wildcard distribution rules, and reserved-card state management.

### TASK-022

- Name: Implement resource-taking action validation
- Priority: High
- Blocked By: None
- Blocks: TASK-026, TASK-030
- Description: Add server-authoritative validation and state mutation for standard Splendor-equivalent resource-taking actions.

### TASK-021

- Name: Implement game start orchestration
- Priority: High
- Blocked By: None
- Blocks: TASK-022, TASK-023, TASK-024, TASK-025, TASK-026
- Description: Initialize turn order, visible market, executives, bank state, and synchronized start-game payloads once the host starts the match.

### TASK-018

- Name: Implement lobby UI and start-game controls
- Priority: Medium
- Blocked By: TASK-017, TASK-020
- Blocks: TASK-021
- Description: Build the waiting-room experience showing joined players, avatars, host status, and start-game controls once minimum player rules are met.

### TASK-020

- Name: Implement realtime room join and lobby presence sync
- Priority: High
- Blocked By: None
- Blocks: TASK-018, TASK-021, TASK-028
- Description: Connect accepted players to a room by slug, synchronize lobby presence, and support disconnect and reconnect behavior before gameplay starts.

### TASK-017

- Name: Implement the pre-join identity and avatar flow
- Priority: High
- Blocked By: TASK-019
- Blocks: TASK-018, TASK-020
- Description: Build the UI and state flow for display name entry, avatar configuration, validation feedback, and accepted join state before websocket connection.

### TASK-019

- Name: Implement join-bootstrap API and temporary player sessions
- Priority: High
- Blocked By: None
- Blocks: TASK-017, TASK-020, TASK-028
- Description: Validate join requests, enforce game-scoped name uniqueness, issue a temporary player session token, and return the data required for safe websocket connection.

### TASK-016

- Name: Build the home page and create-game route flow
- Priority: Medium
- Blocked By: None
- Blocks: TASK-017
- Description: Implement the first frontend user path from landing page to a newly created game URL, preserving the Watercooler tone without starting gameplay work yet.

### TASK-015

- Name: Implement game creation and slug generation API flow
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-019
- Description: Build the API behavior for creating a new game, generating a unique themed slug, and returning the initial redirect target.

### TASK-037

- Name: Enforce unit-test expectations and add baseline coverage
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Update `AGENTS.md` to require unit tests for new code and add baseline automated tests for the existing frontend, API, and realtime scaffolds.

### TASK-035

- Name: Add developer setup, environment examples, and test commands
- Priority: Medium
- Blocked By: TASK-036
- Blocks: None
- Description: Document local setup, configuration, and repeatable developer commands once the first runnable scaffolding and Docker bootstrap exist.

### TASK-036

- Name: Generate Docker-based local development bootstrap
- Priority: High
- Blocked By: TASK-014
- Blocks: None
- Description: Add Dockerfiles and a compose-based local development setup for the frontend, API, realtime service, and MySQL so the scaffolded platform can be started consistently.

### TASK-014

- Name: Design initial MySQL schema and migration strategy
- Priority: High
- Blocked By: None
- Blocks: TASK-015, TASK-019, TASK-020, TASK-021, TASK-036
- Description: Define the first-pass relational model, migration workflow, and seed strategy needed to support games, players, cards, executives, resources, and recovery.

### TASK-013

- Name: Scaffold PHP WebSocket service
- Priority: High
- Blocked By: None
- Blocks: TASK-020, TASK-021
- Description: Establish the long-running realtime service skeleton, connection flow, room lifecycle approach, and shared domain integration points.

### TASK-012

- Name: Scaffold PHP HTTP API service
- Priority: High
- Blocked By: None
- Blocks: TASK-014, TASK-015, TASK-019
- Description: Establish the API entrypoint, routing approach, configuration pattern, and testable service boundaries for request-response features.

### TASK-011

- Name: Scaffold Angular frontend workspace
- Priority: High
- Blocked By: None
- Blocks: TASK-016, TASK-017, TASK-018
- Description: Create the frontend application shell with routing, Angular Material setup, and room for the pre-join, lobby, and game experiences.

### TASK-010

- Name: Define implementation directory layout and service boundaries
- Priority: High
- Blocked By: None
- Blocks: TASK-011, TASK-012, TASK-013, TASK-014
- Description: Finalize the intended folder layout for the Angular frontend, PHP API, PHP realtime service, shared domain logic, and database assets so scaffolding work starts from a clear structure.

### TASK-001

- Name: Convert the original brief into repo planning documents
- Priority: High
- Blocked By: None
- Blocks: TASK-002
- Description: Break the broad project prompt into smaller planning documents under `resources/planning/` so future work can load focused context.

### TASK-002

- Name: Create repository overview and agent operating guide
- Priority: High
- Blocked By: TASK-001
- Blocks: TASK-003
- Description: Add `README.md` and `AGENTS.md` so both humans and agents have a clear starting point for the project.

### TASK-003

- Name: Initialize git and commit the documentation foundation
- Priority: High
- Blocked By: TASK-002
- Blocks: TASK-004
- Description: Start repository history with a documentation-only commit before implementation work begins.

### TASK-004

- Name: Add repo operating files for context, tasks, features, and transcription
- Priority: High
- Blocked By: TASK-003
- Blocks: TASK-010
- Description: Add the context, task-tracking, feature-planning, and transcription operating files plus the initial transcript-maintenance workflow.

### TASK-056

- Name: Fix forced two-token bank edge case
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Allow the take-resources flow to accept two distinct tokens when the bank no longer offers three distinct resource colors, and lock that rule in with backend coverage.

### TASK-057

- Name: Consolidate active board turn and supply layout
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Remove the redundant `Your Turn` box, fold the resource bank and take-resource controls into one panel, and reorganize the board rail into nested rows so the page reads more cleanly during active play.
