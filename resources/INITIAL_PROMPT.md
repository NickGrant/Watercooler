# Watercooler — Full Project Brief for a Programming Agent

## Project Overview

Build a browser-based multiplayer adaptation of a Splendor-style engine-building board game called **Watercooler**.

Watercooler is a humorous **absurd corporate satire** reskin in which players compete to accumulate **Office Prestige** by assembling workplace advantages, attracting executive attention, and climbing the corporate ladder.

This should be a **working multiplayer web application** built with the following stack:

- **Frontend:** Angular
- **Backend API:** PHP
- **Database:** MySQL
- **Realtime Multiplayer:** WebSockets, implemented as a separate PHP long-running service
- **UI Framework:** Angular Material
- **Visual Skin:** retro intranet / Windows-98-inspired corporate portal
- **Avatar Style:** flat vector corporate satire

This is a **party-game-style** application, not an account platform. Users join a game by URL, choose a name and avatar, and play in realtime.

The gameplay should remain **mechanically equivalent to Splendor**, but the theme, terminology, art direction, and UI presentation should be fully adapted to the Watercooler setting.

---

## High-Level Product Goals

The finished project should:

- preserve the core gameplay loop and strategic structure of Splendor
- present the game as a comedic office-politics satire
- support multiplayer play through a shared game URL
- allow users to create a game and immediately join it
- let users pick a name and simple generated avatar before joining
- use server-authoritative realtime gameplay
- feel polished enough to serve as an MVP and a foundation for future expansion

---

## Theme and Tone

## Theme Summary

Watercooler is set inside a ridiculous corporation where players are competing office workers trying to accumulate **Office Prestige**.

Instead of collecting gems and buying developments, players collect office resources and acquire permanent **Workplace Advantages** such as executive sponsorship, calendar control, workflow automation, department alliances, and strategic visibility.

The tone should be:

- absurd corporate satire
- playful and readable
- lightly cynical, but not mean
- exaggerated office jargon
- funny enough to feel memorable, but clear enough to play without confusion

## Tone References

Aim for a tone somewhere between:

- cursed internal corporate portal
- exaggerated office jargon
- satirical management culture
- silly but readable business nonsense

The game should feel like a polished internal tool from an alternate universe where all promotion decisions are made through ritualized project claiming, budget leverage, and executive optics.

---

## Gameplay Adaptation

## Core Design Constraint

The gameplay should remain faithful to Splendor.

This means:

- turn structure should match Splendor
- action choices should match Splendor
- card economy and permanent discount mechanics should match Splendor
- executive/noble behavior should match Splendor
- win condition and endgame trigger should match Splendor
- overall strategic feel should match Splendor

You may rename and re-present components, but do **not** redesign the game into something mechanically different.

## Thematic Renaming

Use the following Watercooler terminology throughout the application.

### Resources
There are 5 standard resource types plus 1 wildcard.

Use these renamed resources:

- **Coffee**
- **Spreadsheets**
- **Budget**
- **Connections**
- **Time**
- **Executive Favor** (wildcard)

These should map consistently to colors and card costs across the game.

### Development Cards
Development cards become **Workplace Advantages**.

These are permanent assets that reduce the cost of future purchases and may grant Office Prestige.

### Nobles
Nobles become **Executives**.

These are high-value leadership figures that are automatically attracted once a player has built the right portfolio of advantages.

### Prestige Points
Victory points are renamed **Office Prestige**.

### Reserve Action
Reserving a card becomes **Claiming a Project**.

### Gold / Wild Token
Gold becomes **Executive Favor**.

---

## Thematic Content Direction

## Resources

Each resource should feel like part of office life.

Suggested interpretations:

- **Coffee** = energy, urgency, hustle
- **Spreadsheets** = analysis, admin, structure
- **Budget** = funding, authority, procurement
- **Connections** = influence, relationships, office politics
- **Time** = scheduling, bandwidth, availability
- **Executive Favor** = wildcard leverage, intervention, sponsorship

## Workplace Advantages

Workplace Advantages should use exaggerated corporate jargon and office-politics humor.

Examples of card naming direction:

- Auto-Fill Template
- Workflow Automation
- Calendar Control
- Budget Sign-Off
- Department Ally
- Executive Sponsor
- Visibility Initiative
- Strategic Alignment Deck
- Meeting Chair Privileges
- Cross-Functional Buy-In
- Personal KPI Dashboard
- Printer Access
- Lunch-and-Learn Circuit
- Synergy Task Force
- Quarterly Initiative Ownership
- Escalation Pathway
- Leadership Exposure
- Operational Readout
- Priority Inbox Channel
- Dashboard Consolidation

These names should sound like ridiculous but recognizable corporate advantages.

## Executives

Executives should feel absurdly important.

Examples:

- VP of Synergy
- Director of Strategic Alignment
- Chief Efficiency Officer
- Senior Vice President of Transformation
- Regional Operations Lead
- Head of Corporate Enablement
- VP of Special Projects
- Chief of Staff
- Executive Steering Committee
- Founder’s Nephew

These should replace nobles mechanically.

---

## Gameplay Rules to Implement

Implement gameplay that behaves like Splendor, using Watercooler language.

## Turn Structure

Players take turns in order.

On a turn, a player may perform one of the standard actions equivalent to Splendor:

1. Take resources from the bank under standard Splendor constraints
2. Claim a Project (reserve a card) and gain an Executive Favor if available
3. Purchase a Workplace Advantage by paying resources, reduced by permanent discounts from previously acquired cards

## Card Effects

Purchased Workplace Advantages should:

- provide a permanent discount in one resource type
- sometimes provide Office Prestige

## Executives

Executives should be awarded automatically when a player qualifies, following normal Splendor-style behavior.

## Endgame

The game should end using standard Splendor logic:

- once a player reaches the target Office Prestige threshold, the endgame is triggered
- the round should complete appropriately
- the winner should be determined according to standard Splendor rules

## Constraints

Allowed:
- rename terms
- adjust wording
- improve readability
- add tooltips and rules help
- use humorous flavor text

Not allowed:
- redesigning the economy
- inventing new action types
- adding unrelated progression systems
- converting the game into asynchronous multiplayer
- changing the core strategy loop

---

## Application Flow

Implement the following flow exactly unless there is a very strong technical reason to slightly adjust it.

### 1. Home Page
When the user arrives at the application, they land on the home page.

The home page should:

- present the Watercooler title and theme
- include a primary CTA: **Create New Game**
- optionally include a secondary CTA: **How to Play**
- communicate the absurd corporate satire tone immediately

### 2. Create New Game
When the user clicks **Create New Game**:

- the backend creates a new game record
- the backend generates a unique human-readable slug made from office jargon terms
- the user is redirected to the game URL

Example format:

- `synergy-report-telemetry`
- `forward-cohesion-logistics`
- `budget-alignment-initiative`
- `calendar-visibility-operations`

The final route should look like:

- `/game/{slug}`

### 3. Game Page Before Join
When a user visits a game page, they are **not yet in the game**.

They should be shown:

- the game slug
- a name input
- a simple avatar generator
- a preview of their chosen avatar
- a **Join** button
- a list of already joined players if any
- lobby status if available

### 4. Join Flow
Before joining, the user must:

- enter a display name
- choose an avatar using the avatar generator

The avatar generator should offer 4–5 options each for:

- body
- face
- hair

When the user clicks **Join**:

- their name and avatar selection are validated
- they are associated with the game
- only then does the frontend connect to the WebSocket service
- the player joins the multiplayer room for that game

### 5. Lobby
After joining, the player enters the lobby state.

The lobby should support:

- showing joined players
- showing each player’s avatar
- identifying the host
- host ability to start the game once enough players are present
- waiting state for players before game start

### 6. Game Start
When the host starts the game:

- initial game state is created
- players are placed in turn order
- visible card market is generated
- executive row is generated
- resource bank is initialized
- all clients receive the synchronized state

### 7. Gameplay
During gameplay:

- clients act through validated server-authoritative actions
- all relevant game updates are broadcast to connected players
- illegal actions are rejected by the server
- UI updates in realtime across all players

---

## Slug Generation Requirements

Game slugs should be themed and memorable.

### Slug Format

Generate game slugs using **3 office-jargon words**:

`{word1}-{word2}-{word3}`

Examples:

- `synergy-report-telemetry`
- `forward-cohesion-logistics`
- `budget-alignment-initiative`
- `calendar-visibility-operations`
- `workflow-optimization-briefing`

### Slug Rules

- lowercase only
- hyphen-separated
- human-readable
- themed to absurd corporate satire
- unique across all games
- checked against the database before use
- regenerated on collision
- optionally append a short numeric suffix only if repeated collisions occur

Example fallback:

- `synergy-report-telemetry-42`

### Slug Word Bank Structure

Use 3 categories so generated slugs sound coherent.

#### Category A — Corporate Verbs / Modifiers
Examples:
- synergy
- forward
- strategic
- dynamic
- agile
- scalable
- optimized
- integrated
- proactive
- aligned
- executive
- premium
- streamlined
- value
- crossfunctional

#### Category B — Office / Planning Nouns
Examples:
- report
- meeting
- budget
- initiative
- roadmap
- calendar
- workflow
- dashboard
- pipeline
- memo
- review
- briefing
- alignment
- deliverable
- projection

#### Category C — Business / Operations Nouns
Examples:
- logistics
- telemetry
- operations
- enablement
- compliance
- analytics
- visibility
- transformation
- strategy
- infrastructure
- efficiency
- governance
- optimization
- engagement
- execution

The system should support swapping or expanding these lists later.

---

## Party Game Constraints

This is a temporary session-based game, not a full platform.

Requirements:

- no user registration
- no persistent account system
- no login flow
- no password management
- players are identified by display name and game-scoped session state
- display names only need to be unique within one game
- avatar selection is stored per player for that game
- rejoin/reconnect support is desirable but can be lightweight

Optional but recommended:

- issue a temporary player session token after join
- store the token locally in the browser
- allow refresh/reconnect into the same game session if still valid

---

## UI / UX Requirements

## UI Direction

Use **Angular Material** as the base component library, but skin it heavily so it does not look like stock Material.

The desired UI style is:

- retro intranet
- faux enterprise portal
- slightly Windows-98 / early-2000s office software
- corporate dashboard with humorous theming
- polished enough to be readable and intentionally styled

## Visual Traits

Use visual motifs such as:

- faux window chrome
- beveled buttons
- stacked panels
- muted office palette
- gray-blue enterprise backgrounds
- spreadsheet-like dividers
- old portal module headers
- status badges
- tooltip-heavy interface
- small office icons like coffee cups, pie charts, printers, folders, name badges, and org charts

The humor should come from framing and copy, not from making the UI hard to use.

## UX Principles

- legal actions must always be clear
- turn state must be obvious
- counts and costs must be immediately readable
- resource availability should be visually distinct
- hovering or tapping on items should explain them
- comedic language should never obscure gameplay meaning
- invalid actions should fail clearly and politely

---

## Avatar Generator Requirements

Before joining, each player must build a simple avatar.

## Style

Avatar art direction:

- flat vector
- corporate satire
- readable at small sizes
- business-casual office worker vibe
- funny but not grotesque
- modular/layered presentation

## Parts

Provide 4–5 options each for:

- body
- face
- hair

### Body examples
- blazer
- shirt-and-tie
- cardigan
- polo
- office-casual

### Face examples
- smile
- stressed
- smug
- exhausted
- corporate-neutral

### Hair examples
- side part
- tidy bob
- executive swoop
- messy startup cut
- comb-over

## Technical Guidance

Prefer composable SVG or layered vector parts.

Store avatar selections as structured configuration data rather than a flat image export.

Example structure:

- body option id
- face option id
- hair option id

---

## Core Screens

## 1. Home Page

Purpose:
- set tone
- create game
- optionally explain premise

Requirements:
- Watercooler title/logo area
- short thematic description
- Create New Game button
- optional How to Play button
- funny but concise copy

## 2. Join / Pre-Lobby Screen

Purpose:
- gather player identity before joining websocket room

Requirements:
- show game slug
- name input
- avatar builder
- avatar preview
- Join button
- validation messages
- existing joined players if already present

## 3. Lobby Screen

Purpose:
- waiting area before gameplay starts

Requirements:
- show current players
- show player avatars
- identify host
- show minimum / current player count
- host can start game
- other players see waiting state

## 4. Main Game Screen

Purpose:
- primary gameplay interface

Requirements:
- clear turn indicator
- visible resource bank
- visible workplace advantage market
- visible executive row
- player panels
- current player highlighting
- available actions
- reserved/claimed projects display
- purchased advantages display
- Office Prestige display
- realtime updates for all players

## 5. Rules / Help Modal

Purpose:
- help players understand the game inside the app

Requirements:
- concise rules explanation
- Watercooler terminology
- mechanically precise guidance
- accessible from lobby and game screen

## 6. Endgame / Results Modal

Purpose:
- announce final result

Requirements:
- show winner
- show final Office Prestige standings
- explain tie-breaks if applicable
- offer buttons for returning home or creating another game

---

## Frontend Requirements

Use Angular for:

- routing
- join flow
- avatar builder
- lobby UI
- game board UI
- player state display
- rules modal
- websocket client integration
- local state management

## Suggested Component Structure

Create a clean, modular component architecture. For example:

- `HomePageComponent`
- `GamePageComponent`
- `JoinPanelComponent`
- `AvatarBuilderComponent`
- `LobbyPanelComponent`
- `GameBoardComponent`
- `PlayerPanelComponent`
- `ResourceBankComponent`
- `CardMarketComponent`
- `ExecutiveRowComponent`
- `RulesDialogComponent`
- `EndgameDialogComponent`

## State Management Guidance

Use a centralized client-side game state service.

The client should render server state and send intent/actions, but should not be the authority on legality.

---

## Backend Requirements

Use PHP for the HTTP API.

The backend should handle:

- game creation
- slug generation
- game lookup by slug
- initial bootstrap data
- join preparation / validation
- persistent state storage
- any non-realtime validation that belongs in the API layer

## Suggested HTTP Endpoints

Design a clean JSON API. Example endpoints:

- `POST /api/games`
- `GET /api/games/{slug}`
- `POST /api/games/{slug}/join-bootstrap`
- `GET /api/games/{slug}/state`

You may add more endpoints as needed for management or bootstrapping, but keep the design simple.

---

## Realtime Architecture Requirements

This project requires realtime multiplayer, so use a **separate PHP WebSocket service**.

Do **not** assume ordinary PHP request/response handling alone is sufficient for persistent realtime socket communication.

## Realtime Responsibilities

The WebSocket service should handle:

- player socket connections
- room membership by game slug
- lobby presence
- start-game event
- player turn actions
- validation dispatch or orchestration
- broadcasting updated authoritative state
- disconnect handling
- optional reconnect handling

## Architectural Model

Use this shape:

- Angular frontend
- PHP HTTP API
- PHP WebSocket service
- MySQL persistence
- server-authoritative game rules

The WebSocket layer may keep some active room state in memory for responsiveness, but the game must still be backed by persisted data so sessions are recoverable.

## Realtime Constraint

Only connect the client websocket **after** the user clicks **Join** and the join is accepted.

This should be part of the join flow, not something that happens immediately on page load.

---

## Server Authority Rules

The server must be authoritative for all meaningful gameplay.

Clients should not be trusted to determine:

- whether an action is legal
- whether a purchase is affordable
- whether a player may take specific resources
- whether executive conditions are met
- whether the game is over

The client may:

- show likely legal actions
- guide user interaction
- prevent obviously invalid clicks
- display optimistic loading states if desired

But the server decides the truth.

---

## Database Requirements

Use MySQL.

Design a schema that can support:

- games
- players
- per-game player participation
- player avatars
- resource bank state
- card decks and face-up market
- executive availability
- purchased cards
- reserved cards
- turn order
- game phase
- turn logs or event logs if useful

## Suggested Table Areas

A possible design could include tables like:

- `games`
- `players`
- `game_players`
- `player_avatars`
- `cards`
- `game_cards`
- `executives`
- `game_executives`
- `player_resources`
- `player_cards`
- `player_reserved_cards`
- `game_resource_bank`
- `game_turns`
- `game_events`

This does not need to be followed literally if a cleaner schema emerges.

## Persistence Rules

Persist enough information to reconstruct the state of any active or completed game.

This includes at minimum:

- game slug
- game status
- player list
- host
- avatar selections
- turn order
- market cards
- executives
- player resources
- purchased cards
- reserved cards
- Office Prestige
- resource bank
- winner/endgame state if finished

---

## Lobby and Session Rules

## Host Assignment

Use a simple host concept.

Recommended behavior:

- first player to successfully join becomes host

The host can start the game once the minimum player requirement is met.

## Player Counts

Support normal Splendor-like player counts.

Make player limits configurable, but default to standard expectations for this type of game.

## Name Rules

Display names:

- must be present
- must be non-empty after trimming
- must be unique within the current game
- do not need to be globally unique

## Reconnect Rules

Reconnect support is optional but recommended.

A practical approach:

- issue a temporary per-player session token on join
- store it in local storage
- allow reconnect to same game if the session is still valid and the game is still active

This should not evolve into full user accounts.

---

## Rules Presentation Requirements

Include an in-app rules explanation written in Watercooler terminology.

The wording should be funny but precise.

Example tone direction:

- “Collect resources, claim projects, and assemble a portfolio of workplace advantages.”
- “Each Workplace Advantage makes related future initiatives easier to secure.”
- “As your influence grows, executives begin to take notice.”
- “The first employee to reach the Office Prestige threshold triggers the endgame.”

Rules help must be understandable to a new player without requiring outside research.

---

## Content and Copy Requirements

Generate a starter content set for the theme.

This should include:

- resource names
- executive names
- workplace advantage names
- UI button labels
- lobby copy
- loading text
- turn prompts
- action confirmations
- empty state text
- endgame text

Humor should be consistent but restrained.

### Example button / action label direction

- Create New Game
- Join
- Claim Project
- Secure Budget
- Leverage Connections
- Await Executive Review
- Circle Back
- Sync Calendar
- Escalate Strategically

Do not overdo the joke to the point of obscuring the action.

---

## Non-Functional Requirements

## Code Quality

The codebase should be:

- modular
- readable
- maintainable
- documented where necessary
- separated cleanly by responsibility
- not dominated by giant monolithic files

## Separation of Concerns

Keep these concerns separate:

- game rules
- theme content
- transport/networking
- persistence
- frontend presentation

It should be possible later to reuse the core rules engine with a different theme.

## Testing

Include testing for the most important logic.

At minimum cover:

- game creation
- slug uniqueness
- join validation
- host assignment
- game start preconditions
- legal and illegal move validation
- purchase validation
- executive award logic
- endgame trigger logic

## Error Handling

Gracefully handle:

- invalid slug
- duplicate player name in same game
- joining a full game
- joining a game in an invalid state
- illegal action attempts
- websocket disconnects
- stale client actions
- missing bootstrap data
- reconnect failure

The user should receive understandable feedback.

---

## Deliverables

The project should include:

1. Angular frontend
2. PHP backend API
3. PHP WebSocket service
4. MySQL schema and migrations
5. Seed data for workplace advantages and executives
6. Basic README with local setup instructions
7. Environment configuration example
8. Minimal but real test coverage
9. Clear game state model
10. Initial Watercooler theme styling

---

## Implementation Priorities

Implement in phases.

## Phase 1 — Foundation
- project skeleton
- Angular app shell
- PHP API skeleton
- MySQL schema
- game creation endpoint
- office-jargon slug generation
- home page
- game route
- join screen
- avatar builder
- join validation

## Phase 2 — Lobby and Session Flow
- lobby UI
- player list
- host assignment
- start-game flow
- websocket connection after join
- room membership by slug
- basic lobby broadcasts

## Phase 3 — Core Gameplay
- initialize decks and market
- initialize resource bank
- initialize executives
- turn order
- player actions
- card purchases
- reserved cards
- executive awards
- Office Prestige tracking
- endgame trigger
- synchronized game state broadcasts

## Phase 4 — Polish
- retro intranet styling
- humorous copy pass
- rules modal
- reconnect support
- better action feedback
- improved validation messaging
- result screen polish

---

## Instruction to the Programming Agent

Build this as a working MVP first.

Prioritize in this order:

1. gameplay correctness
2. multiplayer reliability
3. maintainable architecture
4. clean thematic presentation
5. polish

Do not overengineer.

When uncertain:

- keep gameplay faithful
- keep the code modular
- keep the UI readable
- keep the theme funny but understandable
- document assumptions clearly

Favor a server-authoritative implementation with a simple, testable state model.

The goal is a playable, themed, multiplayer Watercooler MVP that is mechanically faithful to Splendor and built on PHP, MySQL, Angular, and a PHP WebSocket service.