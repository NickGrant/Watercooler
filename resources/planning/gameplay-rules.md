# Gameplay Rules

## Design Constraint

Watercooler must remain mechanically faithful to Splendor. Theme language changes are allowed, but the action economy, discount model, executive acquisition logic, and endgame structure should remain equivalent.

## Thematic Mapping

- Victory points -> Office Prestige
- Development cards -> Workplace Advantages
- Nobles -> Executives
- Reserve action -> Claim Project
- Gold/wild token -> Executive Favor

Resources:

- Coffee
- Spreadsheets
- Budget
- Connections
- Time
- Executive Favor as wildcard

## Required Turn Actions

On a turn, a player may do one standard action:

1. Take resources from the bank under Splendor-equivalent constraints
2. Claim a Project and gain Executive Favor if available
3. Purchase a Workplace Advantage using resources reduced by permanent discounts

## Card And Executive Rules

- Workplace Advantages grant a permanent discount in one resource type
- Some Workplace Advantages also grant Office Prestige
- Executives are awarded automatically when their requirements are met
- The server must determine affordability, eligibility, and awards

## Endgame Rules

- reaching the target Office Prestige threshold triggers the endgame
- the round completes according to normal Splendor structure
- the winner is resolved using standard tie-breaking behavior

## Non-Negotiable Constraints

- no new action types
- no asynchronous turn system
- no side progression loops unrelated to Splendor
- no theme joke that makes mechanical meaning unclear

## Testing Priorities

Future automated tests should cover:

- legal and illegal turn actions
- purchase cost reduction from permanent discounts
- executive award logic
- endgame trigger and winner calculation
- validation of reserve and resource-taking rules
