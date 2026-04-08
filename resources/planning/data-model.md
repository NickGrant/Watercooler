# Data Model

## Persistence Goal

Persist enough information to reconstruct any active or completed game and support reconnect, validation, and endgame review.

## Core Entities

- games
- players
- game_players
- player_avatars
- cards
- game_cards
- executives
- game_executives
- player_resources
- player_cards
- player_reserved_cards
- game_resource_bank
- game_turns
- game_events

## Data Areas To Represent

### Game Metadata

- slug
- status
- phase
- host player
- created and updated timestamps
- winner or completion metadata if finished

### Player Participation

- display name scoped to a game
- turn order
- host flag
- connection/session state
- Office Prestige total

### Avatar Data

- body option id
- face option id
- hair option id

### Board State

- visible market cards
- deck state
- available executives
- bank resource counts

### Player Holdings

- resources in hand
- purchased Workplace Advantages
- reserved cards
- discounts by resource type

## Modeling Notes

- keep theme content separable from rules data when practical
- prefer stable ids for cards, executives, and avatar parts
- keep enough event or turn history to debug or replay critical transitions

## Open Design Choice

The initial bootstrap chooses a pragmatic relational-plus-snapshot hybrid:

1. normalized relational tables for core game state
2. event and turn logs for auditability
3. optional snapshot rows for faster recovery and simpler reconnect support

See `database-strategy.md` and `database/migrations/001_initial_schema.sql` for the concrete first-pass schema.
