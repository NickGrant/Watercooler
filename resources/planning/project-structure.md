# Project Structure

## Purpose

This document fixes the intended implementation layout before service scaffolding begins. It defines where code should live and which runtime owns each responsibility.

## Top-Level Directories

- `frontend/`
  Angular application workspace and UI assets.
- `backend/api/`
  PHP HTTP API application for request-response endpoints.
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
- `backend/api/` may depend on `backend/shared/`.
- `backend/shared/` must not depend on transport-specific code from API adapters or future sync transports.
- `database/` is consumed by backend services, but should not contain service-specific business logic.

## Bootstrapping Rule

All new bootstrap work should land inside these directories instead of introducing new top-level application folders unless there is a strong architectural reason.
