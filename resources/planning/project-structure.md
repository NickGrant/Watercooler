# Project Structure

## Purpose

This document fixes the intended implementation layout before service scaffolding begins. It defines where code should live and which runtime owns each responsibility.

## Top-Level Directories

- `frontend/`
  Angular application workspace and UI assets.
- `backend/api/`
  PHP HTTP API application for request-response endpoints.
- `backend/realtime/`
  Optional PHP realtime transport experiments and related service scaffolding. This is no longer required for the default shared-hosting deployment path.
- `backend/shared/`
  Shared PHP domain code that can be used by multiple backend runtimes without duplicating rules logic.
- `database/`
  Migrations, seeds, schema notes, and local database bootstrap assets.
- `resources/`
  Application-facing planning and documentation.
- `agent/`
  Agent-facing workflow, step, and context files.

## Ownership Boundaries

### `frontend/`

- Angular app shell
- routes and pages
- Angular Material integration
- smart polling and browser-side refresh orchestration
- browser-side session persistence

### `backend/api/`

- HTTP entrypoint and routing
- game creation and slug generation
- join-bootstrap and read endpoints
- configuration loading for request-response services

### `backend/realtime/`

- optional or legacy push-transport experiments
- not required for the current polling-first shared-hosting deployment path
- candidate for removal once no remaining docs or tooling depend on it

### `backend/shared/`

- game rules domain model
- validation logic
- shared config helpers
- data transfer contracts shared between backend services

### `database/`

- SQL migrations
- seed data
- schema snapshots or notes
- local database initialization helpers

## Dependency Direction

- `frontend/` depends on API contracts, but not on backend implementation details.
- `backend/api/` and any future optional transport service may depend on `backend/shared/`.
- `backend/shared/` must not depend on transport-specific code from API or realtime layers.
- `database/` is consumed by backend services, but should not contain service-specific business logic.

## Bootstrapping Rule

All new bootstrap work should land inside these directories instead of introducing new top-level application folders unless there is a strong architectural reason.
