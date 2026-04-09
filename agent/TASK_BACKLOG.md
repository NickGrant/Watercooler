# TASK_BACKLOG.md

## Purpose

This file tracks outstanding work that matters to the project but does not need to stay in the default context window.

## Backlog Tasks

### TASK-027

- Name: Build main game board UI
- Priority: High
- Blocked By: None
- Blocks: TASK-031, TASK-032
- Description: Render the market, bank, executives, player panels, turn state, and action surfaces in a readable real-time layout.

### TASK-028

- Name: Implement reconnect and resync flow
- Priority: Medium
- Blocked By: None
- Blocks: TASK-033
- Description: Allow a player with a valid temporary session to re-enter an in-progress or lobby game without a full account system.

### TASK-029

- Name: Build endgame and results presentation
- Priority: Medium
- Blocked By: TASK-026, TASK-027
- Blocks: None
- Description: Show the winner, final standings, and tie-break explanation with options to leave or start a new session.

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
- Blocks: TASK-031
- Description: Create the initial themed content set for cards, executives, labels, and system copy.
