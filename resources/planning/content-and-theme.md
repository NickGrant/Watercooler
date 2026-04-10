# Content And Theme

## Tone

Watercooler should feel like a polished but cursed internal corporate portal. The humor is absurd, bureaucratic, and office-jargon-heavy, but gameplay meaning must stay readable.

## Content Direction

### Resource Names

- Coffee
- Spreadsheets
- Budget
- Connections
- Time
- Executive Favor

### Workplace Advantage Naming

Use ridiculous but recognizable office-benefit language such as:

- Coffee Spill Cleanup
- Workflow Automation
- Calendar Collision Review
- Budget Sign-Off
- Department Liaison
- Leadership Exposure
- Priority Inbox Channel
- Spreadsheet Reconciliation
- Strategic Alignment Deck
- Executive Sponsor
- Cross-Functional Buy-In
- Meeting Chair Privileges
- Synergy Task Force
- Quarterly Initiative Ownership
- Operational Readout

### Starter Seed Families

The initial seed deck in `database/migrations/002_seed_cards_and_executives.sql` should use a small set of repeatable naming families so the starter content stays readable:

- Tier 1 cards: `Coffee Spill Cleanup`, `Spreadsheet Reconciliation`, `Calendar Collision Review`, `Budget Sign-Off`, `Department Liaison`
- Tier 2 cards: `Workflow Automation`, `Priority Inbox Channel`, `Strategic Alignment Deck`, `Executive Sponsor`, `Cross-Functional Buy-In`
- Tier 3 cards: `Leadership Exposure`, `Meeting Chair Privileges`, `Synergy Task Force`, `Quarterly Initiative Ownership`, `Operational Readout`

`resources/cards.csv` remains the numeric cost matrix reference for those seed rows. The migration is the source of truth for the starter names.

### Executive Naming

Executives should sound self-important and slightly absurd, for example:

- VP of Synergy
- Director of Strategic Alignment
- Chief Efficiency Officer
- Senior Vice President of Transformation
- Regional Operations Lead
- Head of Corporate Enablement
- VP of Special Projects
- Chief of Staff
- Executive Steering Committee
- Founder's Nephew

These should replace nobles mechanically.

## UI Copy Guidance

- keep labels mechanically clear first
- use flavor text lightly
- invalid actions should fail politely and specifically
- lobby and loading text can carry more humor than transactional buttons

## Validation Notes

- Keep `resources/cards.csv`, `database/migrations/002_seed_cards_and_executives.sql`, and this file aligned when starter content changes.
- Use the migration for final seeded names and the CSV for the underlying cost pattern.
- Prefer ASCII apostrophes in seed content and planning docs so the repo stays encoding-safe.

## Visual Direction

- retro intranet
- faux Windows-98 or early-2000s enterprise software cues
- stacked panels, module headers, bevels, badges, muted office palette
- icons such as coffee cups, folders, printers, charts, and org-structure motifs

## Avatar Direction

Avatars should be simple modular flat vectors with 4 to 5 options each for:

- body
- face
- hair

Store avatar choices as configuration ids, not rendered image blobs.
