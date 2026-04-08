# Planning Index

This folder breaks the Watercooler project into smaller planning documents so implementation work can use narrow, relevant context.

## Documents

- `architecture.md`: top-level system design and responsibility boundaries
- `gameplay-rules.md`: rules fidelity, terminology mapping, and game constraints
- `frontend.md`: Angular routes, screens, components, and client-state expectations
- `backend-api.md`: HTTP API scope, endpoint plan, and server responsibilities
- `realtime.md`: WebSocket service responsibilities and state synchronization
- `data-model.md`: MySQL persistence direction and entity breakdown
- `lobby-and-sessions.md`: join flow, host rules, reconnect approach, and player lifecycle
- `content-and-theme.md`: naming, visual direction, tone, and avatar system
- `roadmap.md`: phased implementation sequence

## Usage Guidance

- Start with the smallest document that fits the task.
- Use `architecture.md` plus one focused doc for most implementation work.
- Return to `resources/INITIAL_PROMPT.md` only when broader context is needed.
