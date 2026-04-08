# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-020

- Name: Implement realtime room join and lobby presence sync
- Priority: High
- Blocked By: TASK-017
- Blocks: TASK-018, TASK-021, TASK-028
- Description: Connect accepted players to a room by slug, synchronize lobby presence, and support disconnect and reconnect behavior before gameplay starts.

### TASK-021

- Name: Implement game start orchestration
- Priority: High
- Blocked By: TASK-018, TASK-020
- Blocks: TASK-022, TASK-023, TASK-024, TASK-025, TASK-026
- Description: Initialize turn order, visible market, executives, bank state, and synchronized start-game payloads once the host starts the match.

### TASK-022

- Name: Implement resource-taking action validation
- Priority: High
- Blocked By: TASK-021
- Blocks: TASK-026, TASK-030
- Description: Add server-authoritative validation and state mutation for standard Splendor-equivalent resource-taking actions.

### TASK-023

- Name: Implement project-claiming and Executive Favor flow
- Priority: High
- Blocked By: TASK-021
- Blocks: TASK-026, TASK-030
- Description: Add reserve-equivalent behavior, wildcard distribution rules, and reserved-card state management.

### TASK-024

- Name: Implement Workplace Advantage purchase flow
- Priority: High
- Blocked By: TASK-021
- Blocks: TASK-025, TASK-026, TASK-030
- Description: Add affordability validation, permanent discount application, cost payment, and purchased-card state updates.

### TASK-025

- Name: Implement Executive award and Office Prestige logic
- Priority: High
- Blocked By: TASK-021, TASK-024
- Blocks: TASK-026, TASK-030
- Description: Award executives automatically when requirements are met and maintain correct prestige totals.

### TASK-026

- Name: Implement endgame trigger and winner resolution
- Priority: High
- Blocked By: TASK-021, TASK-022, TASK-023, TASK-024, TASK-025
- Blocks: TASK-029, TASK-030
- Description: Detect the prestige threshold, complete the round correctly, and resolve the winner according to Splendor-equivalent rules.

### TASK-027

- Name: Build main game board UI
- Priority: High
- Blocked By: TASK-021
- Blocks: TASK-031, TASK-032
- Description: Render the market, bank, executives, player panels, turn state, and action surfaces in a readable real-time layout.

### TASK-028

- Name: Implement reconnect and resync flow
- Priority: Medium
- Blocked By: TASK-020
- Blocks: TASK-033
- Description: Allow a player with a valid temporary session to re-enter an in-progress or lobby game without a full account system.

### TASK-029

- Name: Build endgame and results presentation
- Priority: Medium
- Blocked By: TASK-026, TASK-027
- Blocks: None
- Description: Show the winner, final standings, and tie-break explanation with options to leave or start a new session.

### TASK-030

- Name: Add core game rules automated tests
- Priority: High
- Blocked By: TASK-022, TASK-023, TASK-024, TASK-025, TASK-026
- Blocks: None
- Description: Cover legal and illegal move validation, executive logic, and endgame behavior at the rules layer.

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
- Blocks: TASK-029, TASK-031
- Description: Theme the Angular Material base into a readable faux-corporate portal with strong visual identity.

### TASK-033

- Name: Add disconnect, stale-action, and recovery handling
- Priority: Medium
- Blocked By: TASK-028
- Blocks: None
- Description: Handle socket failures, stale client actions, and recovery messaging gracefully across lobby and game states.

### TASK-034

- Name: Seed starter Workplace Advantages and Executives content
- Priority: Medium
- Blocked By: None
- Blocks: TASK-021, TASK-031
- Description: Create the initial themed content set for cards, executives, labels, and system copy.
