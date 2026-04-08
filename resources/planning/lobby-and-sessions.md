# Lobby And Sessions

## Join Flow

Before entering realtime play, a user must:

1. open a game URL
2. enter a display name
3. choose avatar parts
4. submit join
5. receive join acceptance
6. connect to the WebSocket service

## Player Rules

- display names must be present after trimming
- names must be unique within the current game
- no full account system is needed
- player identity is game-scoped

## Host Rules

- the first successful player becomes host
- the host can start the game once minimum player requirements are met
- host responsibilities should remain minimal and explicit

## Lobby Responsibilities

- show current players and avatars
- identify the host
- show readiness to start based on player count and state
- keep waiting players synchronized through realtime updates

## Session Direction

Reconnect support is recommended through a temporary player session token stored in the browser. This should support refresh or short disconnect recovery without turning into an account platform.

## Failure Cases To Handle

- invalid slug
- duplicate display name in the same game
- joining a full game
- joining a game in an invalid phase
- expired or invalid reconnect token
- socket disconnect before game start
- stale client after reconnect
