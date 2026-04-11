# STEP_ARCHIVE.md

## Purpose

This file stores completed work so `agent/STEPS.md` can stay small and useful.

## Completed Steps

### STEP-081

- Name: Build floating in-game bug report panel
- Priority: High
- Blocked By: None
- Blocks: STEP-082
- Description: Add a right-edge floating bug-report tab on the game page that expands into a compact form with optional reply email, required message field, submission feedback, and only the minimum additional client context needed for debugging.

### STEP-080

- Name: Implement bug report submission API flow
- Priority: High
- Blocked By: None
- Blocks: STEP-081, STEP-082
- Description: Add a server-side submission endpoint and service that validates required message content, accepts an optional reply email, resolves the active player and room context, and persists the report in unread state for later DB-side review.

### STEP-079

- Name: Add bug report persistence schema and storage contract
- Priority: High
- Blocked By: None
- Blocks: STEP-080, STEP-081, STEP-082
- Description: Add the database table and backend repository contract for in-game bug reports, storing unread/read status, message content, optional reply email, created timestamp, reporter display name, room slug, and lightweight debugging snapshot fields that are not reliably recoverable later.

### STEP-097

- Name: Rework check-in and waiting-room layout into two columns
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Rebuild the pre-game game-page layout into a 50/50 two-column split with check-in on one side and the waiting room on the other, removing the room snapshot panel entirely.

### STEP-096

- Name: Add collapsible rules section to the home page
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Add a collapsible rules/help surface on the front page so players can review the basic gameplay loop before entering a room.

### STEP-095

- Name: Remove preview game route entry from home page
- Priority: Medium
- Blocked By: None
- Blocks: STEP-096, STEP-097
- Description: Remove the preview-game-route shortcut from the landing page so the create-game path remains the primary entry into the app.

### STEP-094

- Name: Encapsulate step and job workflow into portable skills
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Package the repo's step and job maintenance workflow into portable shared skill definitions so the operating pattern is less tied to one specific agent adapter or one-off doc wording.

### STEP-093

- Name: Rename tracker workflow to STEP and JOB
- Priority: High
- Blocked By: STEP-091
- Blocks: STEP-094
- Description: Rename the agent tracker vocabulary and file set from task and feature terminology to step and job terminology across the repo, updating the existing files, references, and source-of-truth docs to match the new language.

### STEP-092

- Name: Improve transcript readability within portable markdown limits
- Priority: Medium
- Blocked By: STEP-089, STEP-090, STEP-091
- Blocks: None
- Description: Improve at-a-glance readability in the transcript format using portable markdown conventions such as stronger speaker labels and separators, while avoiding viewer-specific HTML or CSS dependencies.

### STEP-090

- Name: Move transcription rules into the transcription skill and make transcript creation resilient
- Priority: High
- Blocked By: STEP-089
- Blocks: STEP-092
- Description: Move as many transcription-maintenance rules as practical into the shared `transcription-sync` skill, reduce the skill's dependency on other repo docs where possible, and ensure the workflow can create the target transcript file if it is missing.

### STEP-091

- Name: Implement transcript memory-management workflow
- Priority: High
- Blocked By: STEP-089
- Blocks: STEP-092, STEP-093
- Description: Add a low-token transcript maintenance strategy, likely based on helper scripts and/or transcript partitioning, so agents can identify the correct append target and update transcript state without repeatedly loading the full conversation history.

### STEP-089

- Name: Start temporary transcript continuation and optimization lane
- Priority: High
- Blocked By: None
- Blocks: STEP-090, STEP-091, STEP-092
- Description: Create a temporary continuation transcript so new conversation stops growing `agent/TRANSCRIPTION.md`, define the transcription-optimization task breakdown, and establish the repo transition path for lower-token transcript maintenance work.

### STEP-088

- Name: Align transcript workflow with shared transcription skill
- Priority: High
- Blocked By: STEP-087
- Blocks: None
- Description: Make the repository transcript workflow fully consistent with the shared `transcription-sync` skill, including any missing structural elements such as sync notes, and document any formatting limits or safe improvements for transcript readability.

### STEP-087

- Name: Expand pre-commit doc maintenance rule
- Priority: High
- Blocked By: STEP-086
- Blocks: None
- Description: Update the commit workflow guidance so agents review and update `README.md`, `AGENTS.md`, and `agent/LLM_CONTEXT.md` whenever a change should affect those source-of-truth docs, keeping the core repo guidance continuously current instead of letting drift accumulate.

### STEP-086

- Name: Remove temporary agent-doc audit artifact
- Priority: Medium
- Blocked By: None
- Blocks: STEP-087, STEP-088
- Description: Remove `agent/AGENT_DOC_AUDIT.md` now that its findings have been folded into the standing operating docs and no longer provide day-to-day operating value.

### STEP-085

- Name: Codify standard feature execution workflow
- Priority: High
- Blocked By: STEP-083
- Blocks: None
- Description: Update the agent operating docs so job work explicitly defaults to evaluating parallelism up front, completing steps with a commit per step, notifying the user when a job is fully done, and pausing only when required information is missing.

### STEP-084

- Name: Refactor agent docs for maintainability and clearer source-of-truth links
- Priority: High
- Blocked By: STEP-083
- Blocks: None
- Description: Apply the audit findings by removing duplicate or low-value guidance, tightening contradictory language, and increasing maintainability through clearer cross-file references and narrower ownership of rules.

### STEP-083

- Name: Audit agent-facing docs for gaps, duplication, and low-value guidance
- Priority: High
- Blocked By: None
- Blocks: STEP-084, STEP-085
- Description: Review `AGENTS.md` and the agent-facing markdown files for missing workflow coverage, contradictory or duplicated guidance, weak source-of-truth boundaries, and sections that add little operational value.

### STEP-078

- Name: Audit CSS ownership and move styles to their closest appropriate home
- Priority: High
- Blocked By: STEP-077
- Blocks: None
- Description: Review the existing frontend SCSS files and fix ownership issues by moving duplicated cross-cutting rules upward for reuse and moving page- or component-specific rules downward into the closest logical stylesheet.

### STEP-077

- Name: Document stylesheet ownership and styling-boundary rules
- Priority: Medium
- Blocked By: None
- Blocks: STEP-078
- Description: Update the workflow guidance so shared tokens and cross-cutting styles live globally while page- and component-specific styles remain in their closest owning stylesheet, even when style budgets are tight.

### STEP-076

- Name: Centralize reusable visual tokens and shared CSS values
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Audit frontend styling for repeated colors and other shared visual constants, move them into global CSS variables where appropriate, and update component styles to reuse those tokens consistently.

### STEP-075

- Name: Improve inline code documentation and API comments
- Priority: Medium
- Blocked By: STEP-073
- Blocks: None
- Description: Add or tighten targeted comments, JSDoc, and PHPDoc in areas where intent, contracts, or non-obvious behavior are hard to infer, while avoiding noisy commentary on self-explanatory code.

### STEP-074

- Name: Expand automated coverage for major gameplay and UI flows
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Audit the current frontend and backend tests against major user-facing and rules-critical flows, then add or strengthen coverage so the main game, lobby, session recovery, and authoritative gameplay behaviors are exercised automatically.

### STEP-073

- Name: Refactor frontend and backend hotspots for cleaner boundaries
- Priority: High
- Blocked By: None
- Blocks: STEP-074, STEP-075
- Description: Review the Angular and PHP code for monolithic files, duplicated logic, and weak object boundaries, then refactor toward clearer components, services, and domain classes that follow DRY and OOP principles without changing gameplay behavior.

### STEP-072

- Name: Remove standalone realtime-service deployment dependency
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Update deployment-oriented code and docs so the app no longer assumes a separately hosted websocket process, including configuration, local-development guidance, and any no-longer-needed realtime transport plumbing.

### STEP-071

- Name: Add adaptive polling intervals and visibility-aware refresh policy
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Tune gameplay and lobby polling behavior so the client refreshes aggressively enough for a 4-to-6-player game while reducing unnecessary request volume when the tab is hidden or the room is idle.

### STEP-070

- Name: Replace websocket gameplay sync with smart polling
- Priority: High
- Blocked By: None
- Blocks: STEP-071, STEP-072
- Description: Remove the browser-facing websocket dependency from active-game synchronization and replace it with an adaptive smart-polling flow that works on shared PHP hosting while preserving server-authoritative gameplay state.

### STEP-065

- Name: Build reusable resource icon component
- Priority: High
- Blocked By: None
- Blocks: STEP-066, STEP-068
- Description: Create a shared resource icon component with regular and small variants using square icon art and a superscript value badge with a solid background so resource display logic is centralized before further board-density cleanup.

### STEP-055

- Name: Implement true websocket gameplay transport
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Replace the current scaffolded/polling-based active-game refresh path with a real websocket transport that pushes authoritative gameplay state changes to every connected client.

### STEP-066

- Name: Tighten project cards and increase per-row density
- Priority: High
- Blocked By: STEP-065
- Blocks: None
- Description: Make project cards narrower and denser so more backlog items can fit on a single row without sacrificing readability.

### STEP-067

- Name: Remove border from take-selected-resources button
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Update the take-selected-resources control styling so it matches the other primary actions instead of reading as a bordered special case.

### STEP-068

- Name: Split resource bank into resources and Executive Favor columns
- Priority: High
- Blocked By: STEP-065
- Blocks: None
- Description: Rework the resource-bank layout so the main resource acquisition flow lives in one column while Executive Favor is separated into its own clearer column.

### STEP-069

- Name: Redesign executive cards from mock reference
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Rebuild the executive card presentation to match the structural intent of `resources/ui-mocks/executive-card-v1/mock.png`, using the portrait as the dominant visual, a bordered/stylistic portrait background treatment, a distinct executive-name band, a separate prestige number block, and requirement icons that visually indicate when their thresholds are satisfied.

### STEP-063

- Name: Rename projects to be more expressive
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Rewrite the seeded project names so backlog and completed-project content feels more expressive and thematic during play without changing gameplay balance.

### STEP-064

- Name: Create screenshot skill for game-screen visual capture
- Priority: Medium
- Blocked By: None
- Blocks: None
- Description: Create a shared agent skill that can trigger a game-screen screenshot through an npm-driven Playwright workflow so UI reviews can be repeated consistently during UAT.

### STEP-054

- Name: Tighten game-page layout and contextual help
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Reduce below-the-fold scrolling on the main game route, remove passive instructional copy from active play, and replace it with tighter layout plus contextual tooltip help.

### STEP-053

- Name: Stabilize active-game state sync and change alerts
- Priority: High
- Blocked By: None
- Blocks: STEP-055
- Description: Ensure the frontend keeps the active game route synchronized with authoritative state, clearly reflects turn ownership and state changes, and surfaces short bottom-of-screen toast notifications when important board changes occur.

### STEP-052

- Name: Expand the game scene to use more of the available viewport
- Priority: High
- Blocked By: STEP-038
- Blocks: None
- Description: Adjust the game screen layout so it uses more of the available horizontal space and brings more of the primary board state above the fold.

### STEP-039

- Name: Resolve validated UAT issues
- Priority: High
- Blocked By: STEP-038
- Blocks: None
- Description: Implement, test, and verify the fixes for confirmed UAT findings once they have been reproduced and scoped.

### STEP-038

- Name: Capture and triage active UAT findings
- Priority: High
- Blocked By: None
- Blocks: STEP-039, STEP-046
- Description: Record incoming UAT issues, confirm expected behavior, reproduce defects, and turn validated findings into implementation steps.

### STEP-051

- Name: Redesign executive cards with a Guess Who style visual treatment
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Redesign the executive cards to feel more visual and character-forward, with a presentation reminiscent of Guess Who style portrait cards.

### STEP-050

- Name: Replace resource text labels with compact iconography
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Replace or supplement verbose resource labels with recognizable iconography so the resource displays become significantly tighter and faster to scan.

### STEP-047

- Name: Auto-update the waiting room roster
- Priority: High
- Blocked By: STEP-038
- Blocks: None
- Description: Make the waiting room player list update automatically so new joins, reconnects, and status changes appear without a manual refresh during UAT.

### STEP-049

- Name: Add padding to the current room state box
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Add internal spacing to the current room state status box so it feels visually balanced with the rest of the screen.

### STEP-048

- Name: Format room slugs as readable title text
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Present the room name in the page title area with spaces instead of hyphens and with each word capitalized so it reads like a real room name.

### STEP-044

- Name: Rework avatar picker order and interaction model
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Change the avatar builder to present options in hair, face, body order and replace the current selectors with image carousel controls that preview each choice clearly.

### STEP-045

- Name: Create avatar option art assets
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Produce or integrate the graphic assets needed to represent each avatar choice in the carousel-based builder.

### STEP-043

- Name: Remove development-oriented copy from the game screen
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Remove placeholder or development-facing text from the game route, including sections like "Authenticated Game State", so the UI reads like a finished product during UAT.

### STEP-042

- Name: Add consistent padding to game screen columns
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Tighten the game board layout so each major column and panel has consistent internal spacing and does not feel cramped during gameplay UAT.

### STEP-041

- Name: Widen landing page h1 treatment
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Adjust the landing page heading layout so the primary h1 has more horizontal room and reads cleanly during UAT review.

### STEP-040

- Name: Remove home page status-grid section
- Priority: Medium
- Blocked By: STEP-038
- Blocks: None
- Description: Remove the status-grid section from the home page so the landing experience stays focused on the primary create-game path during UAT cleanup.

### STEP-046

- Name: Debug create-new-game failure
- Priority: High
- Blocked By: STEP-038
- Blocks: None
- Description: Reproduce and diagnose why the create-new-game flow is failing during UAT, then identify whether the issue is in the frontend request path, API behavior, Docker environment, or persisted data state.

### STEP-031

- Name: Build in-app rules and help surface
- Priority: Medium
- Blocked By: STEP-027
- Blocks: None
- Description: Provide a concise rules modal or help surface using Watercooler terminology while remaining mechanically precise.

### STEP-032

- Name: Apply retro intranet visual skin
- Priority: Medium
- Blocked By: STEP-027
- Blocks: None
- Description: Theme the Angular Material base into a readable faux-corporate portal with strong visual identity.

### STEP-029

- Name: Build endgame and results presentation
- Priority: Medium
- Blocked By: STEP-026, STEP-027
- Blocks: None
- Description: Show the winner, final standings, and tie-break explanation with options to leave or start a new session.

### STEP-027

- Name: Build main game board UI
- Priority: High
- Blocked By: None
- Blocks: STEP-031, STEP-032
- Description: Render the market, bank, executives, player panels, turn state, and action surfaces in a readable real-time layout.
### STEP-034

- Name: Seed starter Workplace Advantages and Executives content
- Priority: Medium
- Blocked By: None
- Blocks: STEP-031
- Description: Create the initial themed content set for cards, executives, labels, and system copy.

### STEP-028

- Name: Implement reconnect and resync flow
- Priority: Medium
- Blocked By: None
- Blocks: STEP-033
- Description: Allow a player with a valid temporary session to re-enter an in-progress or lobby game without a full account system.

### STEP-033

- Name: Add disconnect, stale-action, and recovery handling
- Priority: Medium
- Blocked By: STEP-028
- Blocks: None
- Description: Handle socket failures, stale client actions, and recovery messaging gracefully across lobby and game states.

### STEP-030

- Name: Add core game rules automated tests
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Cover legal and illegal move validation, executive logic, and endgame behavior at the rules layer.

### STEP-026

- Name: Implement endgame trigger and winner resolution
- Priority: High
- Blocked By: None
- Blocks: STEP-029, STEP-030
- Description: Detect the prestige threshold, complete the round correctly, and resolve the winner according to Splendor-equivalent rules.

### STEP-025

- Name: Implement Executive award and Office Prestige logic
- Priority: High
- Blocked By: None
- Blocks: STEP-026, STEP-030
- Description: Award executives automatically when requirements are met and maintain correct prestige totals.

### STEP-024

- Name: Implement Workplace Advantage purchase flow
- Priority: High
- Blocked By: None
- Blocks: STEP-025, STEP-026, STEP-030
- Description: Add affordability validation, permanent discount application, cost payment, and purchased-card state updates.

### STEP-023

- Name: Implement project-claiming and Executive Favor flow
- Priority: High
- Blocked By: None
- Blocks: STEP-026, STEP-030
- Description: Add reserve-equivalent behavior, wildcard distribution rules, and reserved-card state management.

### STEP-022

- Name: Implement resource-taking action validation
- Priority: High
- Blocked By: None
- Blocks: STEP-026, STEP-030
- Description: Add server-authoritative validation and state mutation for standard Splendor-equivalent resource-taking actions.

### STEP-021

- Name: Implement game start orchestration
- Priority: High
- Blocked By: None
- Blocks: STEP-022, STEP-023, STEP-024, STEP-025, STEP-026
- Description: Initialize turn order, visible market, executives, bank state, and synchronized start-game payloads once the host starts the match.

### STEP-018

- Name: Implement lobby UI and start-game controls
- Priority: Medium
- Blocked By: STEP-017, STEP-020
- Blocks: STEP-021
- Description: Build the waiting-room experience showing joined players, avatars, host status, and start-game controls once minimum player rules are met.

### STEP-020

- Name: Implement realtime room join and lobby presence sync
- Priority: High
- Blocked By: None
- Blocks: STEP-018, STEP-021, STEP-028
- Description: Connect accepted players to a room by slug, synchronize lobby presence, and support disconnect and reconnect behavior before gameplay starts.

### STEP-017

- Name: Implement the pre-join identity and avatar flow
- Priority: High
- Blocked By: STEP-019
- Blocks: STEP-018, STEP-020
- Description: Build the UI and state flow for display name entry, avatar configuration, validation feedback, and accepted join state before websocket connection.

### STEP-019

- Name: Implement join-bootstrap API and temporary player sessions
- Priority: High
- Blocked By: None
- Blocks: STEP-017, STEP-020, STEP-028
- Description: Validate join requests, enforce game-scoped name uniqueness, issue a temporary player session token, and return the data required for safe websocket connection.

### STEP-016

- Name: Build the home page and create-game route flow
- Priority: Medium
- Blocked By: None
- Blocks: STEP-017
- Description: Implement the first frontend user path from landing page to a newly created game URL, preserving the Watercooler tone without starting gameplay work yet.

### STEP-015

- Name: Implement game creation and slug generation API flow
- Priority: High
- Blocked By: None
- Blocks: STEP-016, STEP-019
- Description: Build the API behavior for creating a new game, generating a unique themed slug, and returning the initial redirect target.

### STEP-037

- Name: Enforce unit-test expectations and add baseline coverage
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Update `AGENTS.md` to require unit tests for new code and add baseline automated tests for the existing frontend, API, and realtime scaffolds.

### STEP-035

- Name: Add developer setup, environment examples, and test commands
- Priority: Medium
- Blocked By: STEP-036
- Blocks: None
- Description: Document local setup, configuration, and repeatable developer commands once the first runnable scaffolding and Docker bootstrap exist.

### STEP-036

- Name: Generate Docker-based local development bootstrap
- Priority: High
- Blocked By: STEP-014
- Blocks: None
- Description: Add Dockerfiles and a compose-based local development setup for the frontend, API, realtime service, and MySQL so the scaffolded platform can be started consistently.

### STEP-014

- Name: Design initial MySQL schema and migration strategy
- Priority: High
- Blocked By: None
- Blocks: STEP-015, STEP-019, STEP-020, STEP-021, STEP-036
- Description: Define the first-pass relational model, migration workflow, and seed strategy needed to support games, players, cards, executives, resources, and recovery.

### STEP-013

- Name: Scaffold PHP WebSocket service
- Priority: High
- Blocked By: None
- Blocks: STEP-020, STEP-021
- Description: Establish the long-running realtime service skeleton, connection flow, room lifecycle approach, and shared domain integration points.

### STEP-012

- Name: Scaffold PHP HTTP API service
- Priority: High
- Blocked By: None
- Blocks: STEP-014, STEP-015, STEP-019
- Description: Establish the API entrypoint, routing approach, configuration pattern, and testable service boundaries for request-response jobs.

### STEP-011

- Name: Scaffold Angular frontend workspace
- Priority: High
- Blocked By: None
- Blocks: STEP-016, STEP-017, STEP-018
- Description: Create the frontend application shell with routing, Angular Material setup, and room for the pre-join, lobby, and game experiences.

### STEP-010

- Name: Define implementation directory layout and service boundaries
- Priority: High
- Blocked By: None
- Blocks: STEP-011, STEP-012, STEP-013, STEP-014
- Description: Finalize the intended folder layout for the Angular frontend, PHP API, PHP realtime service, shared domain logic, and database assets so scaffolding work starts from a clear structure.

### STEP-001

- Name: Convert the original brief into repo planning documents
- Priority: High
- Blocked By: None
- Blocks: STEP-002
- Description: Break the broad project prompt into smaller planning documents under `resources/planning/` so future work can load focused context.

### STEP-002

- Name: Create repository overview and agent operating guide
- Priority: High
- Blocked By: STEP-001
- Blocks: STEP-003
- Description: Add `README.md` and `AGENTS.md` so both humans and agents have a clear starting point for the project.

### STEP-003

- Name: Initialize git and commit the documentation foundation
- Priority: High
- Blocked By: STEP-002
- Blocks: STEP-004
- Description: Start repository history with a documentation-only commit before implementation work begins.

### STEP-004

- Name: Add repo operating files for context, steps, jobs, and transcription
- Priority: High
- Blocked By: STEP-003
- Blocks: STEP-010
- Description: Add the context, step-tracking, job-planning, and transcription operating files plus the initial transcript-maintenance workflow.

### STEP-056

- Name: Fix forced two-token bank edge case
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Allow the take-resources flow to accept two distinct tokens when the bank no longer offers three distinct resource colors, and lock that rule in with backend coverage.

### STEP-057

- Name: Consolidate active board turn and supply layout
- Priority: High
- Blocked By: None
- Blocks: None
- Description: Remove the redundant `Your Turn` box, fold the resource bank and take-resource controls into one panel, and reorganize the board rail into nested rows so the page reads more cleanly during active play.
