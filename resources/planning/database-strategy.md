# Database Strategy

## Purpose

This document defines the initial database direction for Watercooler and explains how the first migration is intended to be used.

## Chosen Approach

Use a relational-first MySQL design with:

- normalized core entities for games, players, cards, executives, and resources
- append-only turn and event records for auditability
- optional `game_state_snapshots` rows for recovery and faster state rebuilds

This keeps the MVP queryable and debuggable without forcing a full event-sourced system on day one.

## Migration Strategy

- Store ordered SQL migrations under `database/migrations/`.
- Track applied versions in `schema_migrations`.
- Keep migrations forward-only during bootstrap.
- Prefer additive follow-up migrations instead of editing old migration files after they have been committed.
- Store seed scripts under `database/seeds/`, with content-focused seed data added in later tasks.

## Initial Schema Coverage

The first migration creates tables for:

- games
- players
- player avatars
- game players
- cards
- executives
- game cards
- game executives
- game resource bank
- player resources
- game turns
- game events
- game state snapshots

## Important Modeling Decisions

- `game_players.display_name` is unique within each game so account systems stay unnecessary.
- permanent discounts are stored on `game_players` for fast reads even though they can be derived from purchased cards.
- `game_cards` models deck, market, reserved, and purchased states in one table to reduce duplicated ownership structures.
- `game_state_snapshots` supports pragmatic recovery without making every read reconstruct from the full event log.

## Near-Term Follow-Ups

- seed starter cards and executives in TASK-034
- wire actual migration execution into Docker and local setup in TASK-036 and TASK-035
- connect game creation and join-bootstrap flows to this schema in TASK-015 and TASK-019
