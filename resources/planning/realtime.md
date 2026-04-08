# Realtime Plan

## Purpose

Realtime multiplayer must run through a separate long-running PHP WebSocket service.

## Core Responsibilities

- accept player socket connections after join succeeds
- assign clients to rooms by game slug
- broadcast lobby presence updates
- handle host start-game requests
- receive player turn actions
- validate or dispatch actions through authoritative game logic
- broadcast updated synchronized state
- manage disconnect and reconnect behavior

## Connection Rule

Clients must not connect to the WebSocket service on page load. Connection happens only after the player submits a valid join flow and the server accepts it.

## State Model Direction

- active room state may be cached in memory for responsiveness
- database persistence must remain sufficient for recovery
- state transitions should be serial and deterministic

## Event Categories

Likely event groups:

- connection/authentication
- lobby presence
- game start
- gameplay action intents
- state snapshots or diffs
- errors and validation failures
- reconnect/resync

## Realtime Risks

- concurrent action handling causing invalid state transitions
- room state drifting from persisted state
- unclear reconnect ownership when a player refreshes
- sending partial updates that are hard for clients to reconcile
