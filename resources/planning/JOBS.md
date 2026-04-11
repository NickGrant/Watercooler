# JOBS.md

## Purpose

This file maps major product and repo-operating jobs to the steps that deliver them.

## Jobs

### JOB-001

- Name: Repo Operations And Collaboration Workflow
- Priority: High
- Status: Complete
- Description: Keep the repository easy for humans and agents to navigate through context rules, step tracking, and conversation transcription.
- Steps: STEP-001, STEP-002, STEP-003, STEP-004, STEP-037

### JOB-002

- Name: Platform Scaffolding
- Priority: High
- Status: Complete
- Description: Establish the frontend, API, database structure, and containerized local development setup required to begin implementation safely.
- Steps: STEP-010, STEP-011, STEP-012, STEP-013, STEP-014, STEP-036, STEP-035

### JOB-003

- Name: Game Creation And Entry Flow
- Priority: High
- Status: Complete
- Description: Let a user create a game, receive a themed slug, open the game route, and complete the pre-join identity flow.
- Steps: STEP-015, STEP-016, STEP-017, STEP-019

### JOB-004

- Name: Lobby And Session Management
- Priority: High
- Status: Complete
- Description: Support player presence, host controls, start-game readiness, and lightweight reconnect behavior without full accounts.
- Steps: STEP-018, STEP-020, STEP-021, STEP-028, STEP-033

### JOB-005

- Name: Server-Authoritative Gameplay
- Priority: High
- Status: Complete
- Description: Implement Splendor-equivalent rules for actions, purchases, executive awards, prestige, and endgame resolution.
- Steps: STEP-022, STEP-023, STEP-024, STEP-025, STEP-026, STEP-030

### JOB-006

- Name: Main Game Interface
- Priority: High
- Status: Complete
- Description: Present the real-time game state clearly across the board, player panels, action surfaces, rules, and results.
- Steps: STEP-027, STEP-029, STEP-031, STEP-032

### JOB-007

- Name: Theme Content And Seeds
- Priority: Medium
- Status: Complete
- Description: Provide the themed card names, executive content, avatar direction, and copy needed for the Watercooler presentation layer.
- Steps: STEP-034, STEP-031, STEP-032

### JOB-008

- Name: Persistence And Recovery
- Priority: High
- Status: Complete
- Description: Persist enough state to support active gameplay, reconnects, and post-game reconstruction.
- Steps: STEP-014, STEP-019, STEP-020, STEP-021, STEP-028, STEP-033

### JOB-009

- Name: UAT Cleanup And Stabilization
- Priority: High
- Status: Complete
- Description: Capture user-acceptance findings, fix defects, smooth rough edges, and harden the MVP experience before broader testing or release.
- Steps: STEP-038, STEP-039, STEP-040, STEP-041, STEP-042, STEP-043, STEP-044, STEP-045, STEP-046, STEP-047, STEP-048, STEP-049, STEP-050, STEP-051, STEP-052, STEP-054, STEP-056, STEP-057

### JOB-010

- Name: Gameplay UI Density And Interaction Polish
- Priority: High
- Status: Complete
- Description: Tighten the active-game interface so the board, player economy, and resource-taking flow fit more clearly and more densely without losing usability.
- Steps: STEP-065, STEP-066, STEP-067, STEP-068, STEP-069

### JOB-011

- Name: Content Naming And Visual QA Tooling
- Priority: Medium
- Status: Complete
- Description: Improve the thematic readability of project content and give agents a repeatable screenshot workflow for visual review during UAT.
- Steps: STEP-063, STEP-064

### JOB-012

- Name: Realtime Gameplay Transport
- Priority: High
- Status: Complete
- Description: Transition the active game from polling-based state refresh toward a true websocket transport that pushes authoritative gameplay updates to connected clients.
- Steps: STEP-053, STEP-055

### JOB-013

- Name: Shared-Hosting Smart Polling Compatibility
- Priority: High
- Status: Complete
- Description: Rework passive game synchronization so Watercooler remains responsive for small rooms on simple shared PHP hosting without requiring a dedicated websocket service or extra exposed ports.
- Steps: STEP-070, STEP-071, STEP-072

### JOB-014

- Name: Codebase Cleanup And Maintainability Pass
- Priority: High
- Status: Complete
- Description: Improve code quality across the frontend and backend by tightening architectural boundaries, expanding meaningful automated coverage, documenting non-obvious behavior, and centralizing shared styling tokens.
- Steps: STEP-073, STEP-074, STEP-075, STEP-076

### JOB-015

- Name: Stylesheet Ownership And CSS Placement Audit
- Priority: High
- Status: Complete
- Description: Tighten stylesheet organization by documenting CSS placement rules and auditing the frontend styles so shared rules move upward only when genuinely reused while page- and component-specific rules stay in their closest logical home.
- Steps: STEP-077, STEP-078

### JOB-016

- Name: In-Game Bug Report Capture
- Priority: High
- Status: Complete
- Description: Let players file bug reports directly from the game page through a lightweight floating form that stores unread reports for later DB-side triage, along with the minimum context needed to understand what state they were in when the issue occurred.
- Steps: STEP-079, STEP-080, STEP-081, STEP-082

### JOB-017

- Name: Agent Documentation Audit And Consolidation
- Priority: High
- Status: Complete
- Description: Review the agent-facing operating docs for missing coverage, duplication, contradictions, and low-value content, then consolidate the guidance so responsibilities and cross-file references stay maintainable as the repo evolves.
- Steps: STEP-083, STEP-084, STEP-085, STEP-086, STEP-087, STEP-088

### JOB-018

- Name: Transcript Portability And Memory Management
- Priority: High
- Status: Complete
- Description: Reduce the token and maintenance cost of transcript updates by moving more transcription behavior into the shared skill, improving portability, introducing a lower-cost append workflow, and using a temporary continuation transcript while the migration is underway.
- Steps: STEP-089, STEP-090, STEP-091, STEP-092

### JOB-019

- Name: Portable Step And Job Workflow
- Priority: High
- Status: Complete
- Description: Rename the repo's tracker workflow into step and job terminology and package the maintenance behavior into portable shared skills rather than keeping it only in repo-local doc wording.
- Steps: STEP-093, STEP-094

### JOB-020

- Name: Frontend Entry And Waiting Room Cleanup
- Priority: High
- Status: Complete
- Description: Simplify the landing and pre-game UI by removing the preview route shortcut, adding an upfront rules surface, and reshaping the game waiting-room shell into a cleaner two-column check-in and roster layout.
- Steps: STEP-095, STEP-096, STEP-097

### JOB-021

- Name: Active Player Display Refinement
- Priority: High
- Status: Complete
- Description: Refine the active player's presentation so the current user panel feels lighter, denser, and more intentional without losing key game information.
- Steps: STEP-098

### JOB-022

- Name: Resource Bank Visual Refinement
- Priority: High
- Status: Complete
- Description: Rework the resource bank around larger icon-led controls so the supply area reads more like a tactile action surface than a row of small buttons.
- Steps: STEP-099

### JOB-023

- Name: Backlog Lane Layout Refinement
- Priority: Medium
- Status: Complete
- Description: Tighten backlog card wrapping and lane composition so each tier reads as a deliberate grid instead of a broken row.
- Steps: STEP-100

### JOB-024

- Name: Executive Card Presentation Refinement
- Priority: High
- Status: Complete
- Description: Strengthen executive card hierarchy through a taller portrait treatment, more prominent requirement placement, and a layered title treatment.
- Steps: STEP-101

### JOB-025

- Name: Backend Action Runtime Stabilization
- Priority: High
- Status: Complete
- Description: Resolve the runtime schema mismatch causing live start and game-action requests to fail under the real PDO-backed API environment.
- Steps: STEP-102

### JOB-026

- Name: Polling Architecture Cleanup And Documentation Refresh
- Priority: High
- Status: Complete
- Description: Remove obsolete websocket-era architecture references and cleanup leftover deployment surface so the repo's docs, structure, and guidance reflect the current polling-first shared-hosting path.
- Steps: STEP-103, STEP-104, STEP-105
