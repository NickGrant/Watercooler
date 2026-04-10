# Realtime Plan

## Purpose

Realtime synchronization should preserve a server-authoritative multiplayer flow without requiring a separately hosted transport process on simple shared PHP hosting.

## Core Responsibilities

- refresh room state safely after join succeeds
- keep lobby presence and active-game state synchronized through authenticated HTTP requests
- handle host start-game requests
- validate gameplay actions through authoritative game logic
- recover cleanly from stale or interrupted clients
- leave room for a future push-based transport if hosting changes

## Connection Rule

Clients must not attempt passive synchronization until the player has completed the join flow or restored a valid temporary session.

## State Model Direction

- database persistence must remain sufficient for recovery
- state transitions should be serial and deterministic

## Current Implementation Direction

- gameplay actions remain HTTP/API-driven and server-authoritative
- the browser uses smart polling against the authenticated state endpoint after join-bootstrap or session restore succeeds
- polling cadence adapts between lobby, active turns, and hidden tabs to balance responsiveness with shared-hosting limits
- immediate refreshes still happen after local gameplay actions and stale-action recovery paths

## Event Categories

Likely event groups:

- authentication/session recovery
- lobby presence
- game start
- gameplay action intents
- state snapshots
- errors and validation failures
- reconnect/resync

## Realtime Risks

- excessive poll volume if cadence is too aggressive
- room state drifting from persisted state after stale clients act on old information
- unclear reconnect ownership when a player refreshes
- sluggish player feedback if polling cadence is too conservative
