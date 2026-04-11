# FEATURES.md

## Purpose

This file maps major product and repo-operating features to the tasks that deliver them.

## Features

### FEATURE-001

- Name: Repo Operations And Collaboration Workflow
- Priority: High
- Status: Complete
- Description: Keep the repository easy for humans and agents to navigate through context rules, task tracking, and conversation transcription.
- Tasks: TASK-001, TASK-002, TASK-003, TASK-004, TASK-037

### FEATURE-002

- Name: Platform Scaffolding
- Priority: High
- Status: Complete
- Description: Establish the frontend, API, realtime service, database structure, and containerized local development setup required to begin implementation safely.
- Tasks: TASK-010, TASK-011, TASK-012, TASK-013, TASK-014, TASK-036, TASK-035

### FEATURE-003

- Name: Game Creation And Entry Flow
- Priority: High
- Status: Complete
- Description: Let a user create a game, receive a themed slug, open the game route, and complete the pre-join identity flow.
- Tasks: TASK-015, TASK-016, TASK-017, TASK-019

### FEATURE-004

- Name: Lobby And Session Management
- Priority: High
- Status: Complete
- Description: Support player presence, host controls, start-game readiness, and lightweight reconnect behavior without full accounts.
- Tasks: TASK-018, TASK-020, TASK-021, TASK-028, TASK-033

### FEATURE-005

- Name: Server-Authoritative Gameplay
- Priority: High
- Status: Complete
- Description: Implement Splendor-equivalent rules for actions, purchases, executive awards, prestige, and endgame resolution.
- Tasks: TASK-022, TASK-023, TASK-024, TASK-025, TASK-026, TASK-030

### FEATURE-006

- Name: Main Game Interface
- Priority: High
- Status: Complete
- Description: Present the real-time game state clearly across the board, player panels, action surfaces, rules, and results.
- Tasks: TASK-027, TASK-029, TASK-031, TASK-032

### FEATURE-007

- Name: Theme Content And Seeds
- Priority: Medium
- Status: Complete
- Description: Provide the themed card names, executive content, avatar direction, and copy needed for the Watercooler presentation layer.
- Tasks: TASK-034, TASK-031, TASK-032

### FEATURE-008

- Name: Persistence And Recovery
- Priority: High
- Status: Complete
- Description: Persist enough state to support active gameplay, reconnects, and post-game reconstruction.
- Tasks: TASK-014, TASK-019, TASK-020, TASK-021, TASK-028, TASK-033

### FEATURE-009

- Name: UAT Cleanup And Stabilization
- Priority: High
- Status: Complete
- Description: Capture user-acceptance findings, fix defects, smooth rough edges, and harden the MVP experience before broader testing or release.
- Tasks: TASK-038, TASK-039, TASK-040, TASK-041, TASK-042, TASK-043, TASK-044, TASK-045, TASK-046, TASK-047, TASK-048, TASK-049, TASK-050, TASK-051, TASK-052, TASK-054, TASK-056, TASK-057

### FEATURE-010

- Name: Gameplay UI Density And Interaction Polish
- Priority: High
- Status: Complete
- Description: Tighten the active-game interface so the board, player economy, and resource-taking flow fit more clearly and more densely without losing usability.
- Tasks: TASK-065, TASK-066, TASK-067, TASK-068, TASK-069

### FEATURE-011

- Name: Content Naming And Visual QA Tooling
- Priority: Medium
- Status: Complete
- Description: Improve the thematic readability of project content and give agents a repeatable screenshot workflow for visual review during UAT.
- Tasks: TASK-063, TASK-064

### FEATURE-012

- Name: Realtime Gameplay Transport
- Priority: High
- Status: Complete
- Description: Transition the active game from polling-based state refresh toward a true websocket transport that pushes authoritative gameplay updates to connected clients.
- Tasks: TASK-053, TASK-055

### FEATURE-013

- Name: Shared-Hosting Smart Polling Compatibility
- Priority: High
- Status: Complete
- Description: Rework passive game synchronization so Watercooler remains responsive for small rooms on simple shared PHP hosting without requiring a dedicated websocket service or extra exposed ports.
- Tasks: TASK-070, TASK-071, TASK-072

### FEATURE-014

- Name: Codebase Cleanup And Maintainability Pass
- Priority: High
- Status: Complete
- Description: Improve code quality across the frontend and backend by tightening architectural boundaries, expanding meaningful automated coverage, documenting non-obvious behavior, and centralizing shared styling tokens.
- Tasks: TASK-073, TASK-074, TASK-075, TASK-076
