# Planning Index

This folder breaks the Watercooler project into smaller planning documents so implementation work can use narrow, relevant context.

## Documents

- `architecture.md`: top-level system design and responsibility boundaries
- `JOBS.md`: job map linking application workstreams to step IDs
- `gameplay-rules.md`: rules fidelity, terminology mapping, and game constraints
- `project-structure.md`: intended implementation layout and service boundaries
- `frontend.md`: Angular routes, screens, components, and client-state expectations
- `backend-api.md`: HTTP API scope, endpoint plan, and server responsibilities
- `database-strategy.md`: MySQL migration and schema direction
- `data-model.md`: MySQL persistence direction and entity breakdown
- `lobby-and-sessions.md`: join flow, host rules, reconnect approach, and player lifecycle
- `content-and-theme.md`: naming, visual direction, tone, and avatar system
- `roadmap.md`: phased implementation sequence

## Usage Guidance

- Start with the smallest document that fits the step.
- Use `architecture.md` plus one focused doc for most implementation work.
- Return to `resources/INITIAL_PROMPT.md` only when broader context is needed.
