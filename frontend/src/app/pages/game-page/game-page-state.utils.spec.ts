import { ActiveGameState } from '../../core/models/active-game-state.model';
import {
  buildFinalTieBreakSummary,
  canAddResourceToSelection,
  canAffordCard,
  describeStateChanges,
  executiveFavorRequired,
  formatRoomName,
  sortPlayersByFinalStanding
} from './game-page-state.utils';

describe('game-page-state utils', () => {
  it('formats room slugs into title text', () => {
    expect(formatRoomName('synergy-report-telemetry')).toBe('Synergy Report Telemetry');
    expect(formatRoomName('')).toBe('Unknown Room');
  });

  it('sorts final standings by prestige, purchased cards, and seat order', () => {
    const standings = sortPlayersByFinalStanding(createState().players);

    expect(standings.map((player) => player.displayName)).toEqual(['Pam', 'Jim', 'Dwight']);
    expect(buildFinalTieBreakSummary(standings)).toContain('fewer completed projects');
  });

  it('describes purchased cards and turn changes', () => {
    const previous = createState();
    const next = createState({
      currentTurnGamePlayerId: 1,
      players: [
        {
          ...createState().players[0],
          purchasedCards: [
            {
              code: 'budget-sign-off',
              tier: 1,
              name: 'Budget Sign-off',
              resourceDiscount: 'budget',
              officePrestige: 1,
              cost: {
                coffee: 0,
                spreadsheets: 2,
                budget: 1,
                connections: 0,
                time: 0
              }
            }
          ],
          purchasedCardCount: 1
        },
        createState().players[1],
        createState().players[2]
      ]
    });

    expect(describeStateChanges(previous, next, 1)).toBe(
      'Pam acquired Budget Sign-off. It is now your turn.'
    );
    expect(describeStateChanges(previous, next, 2)).toBe(
      "Pam acquired Budget Sign-off. It is now Pam's turn."
    );
  });

  it('uses executive favor to cover shortfalls when checking affordability', () => {
    const player = {
      ...createState().players[0],
      resources: {
        coffee: 0,
        spreadsheets: 1,
        budget: 0,
        connections: 0,
        time: 0,
        executiveFavor: 2,
        totalTokens: 3
      },
      permanentDiscounts: {
        coffee: 1,
        spreadsheets: 0,
        budget: 0,
        connections: 0,
        time: 0
      }
    };

    expect(
      canAffordCard(player, {
        code: 'budget-buffer',
        tier: 1,
        name: 'Budget Buffer',
        resourceDiscount: 'coffee',
        officePrestige: 1,
        cost: {
          coffee: 2,
          spreadsheets: 1,
          budget: 1,
          connections: 0,
          time: 0
        }
      })
    ).toBeTrue();
  });

  it('reports how much executive favor a purchase will spend', () => {
    const player = {
      ...createState().players[0],
      resources: {
        coffee: 0,
        spreadsheets: 1,
        budget: 0,
        connections: 0,
        time: 0,
        executiveFavor: 2,
        totalTokens: 3
      },
      permanentDiscounts: {
        coffee: 1,
        spreadsheets: 0,
        budget: 0,
        connections: 0,
        time: 0
      }
    };

    expect(
      executiveFavorRequired(player, {
        code: 'budget-buffer',
        tier: 1,
        name: 'Budget Buffer',
        resourceDiscount: 'coffee',
        officePrestige: 1,
        cost: {
          coffee: 2,
          spreadsheets: 1,
          budget: 1,
          connections: 0,
          time: 0
        }
      })
    ).toBe(2);
  });

  it('allows adding a second matching resource only when the bank supports a double take', () => {
    expect(canAddResourceToSelection(['coffee'], 'coffee', createState().bank)).toBeTrue();
    expect(
      canAddResourceToSelection(['coffee'], 'coffee', {
        ...createState().bank,
        coffee: 3
      })
    ).toBeFalse();
  });

  it('supports building toward a three-distinct selection one click at a time', () => {
    expect(canAddResourceToSelection([], 'coffee', createState().bank)).toBeTrue();
    expect(canAddResourceToSelection(['coffee'], 'budget', createState().bank)).toBeTrue();
    expect(canAddResourceToSelection(['coffee', 'budget'], 'time', createState().bank)).toBeTrue();
    expect(canAddResourceToSelection(['coffee', 'budget'], 'coffee', createState().bank)).toBeFalse();
  });
});

function createState(overrides: Partial<ActiveGameState> = {}): ActiveGameState {
  return {
    currentTurnGamePlayerId: 2,
    players: [
      {
        gamePlayerId: 1,
        displayName: 'Pam',
        isHost: true,
        joinStatus: 'connected',
        seatOrder: 1,
        officePrestige: 12,
        resources: {
          coffee: 1,
          spreadsheets: 1,
          budget: 1,
          connections: 0,
          time: 0,
          executiveFavor: 0,
          totalTokens: 3
        },
        permanentDiscounts: {
          coffee: 0,
          spreadsheets: 0,
          budget: 0,
          connections: 0,
          time: 0
        },
        reservedCards: [],
        purchasedCards: [],
        purchasedCardCount: 6,
        claimedExecutives: [],
        claimedExecutiveCount: 0
      },
      {
        gamePlayerId: 2,
        displayName: 'Jim',
        isHost: false,
        joinStatus: 'connected',
        seatOrder: 2,
        officePrestige: 12,
        resources: {
          coffee: 0,
          spreadsheets: 0,
          budget: 0,
          connections: 1,
          time: 1,
          executiveFavor: 0,
          totalTokens: 2
        },
        permanentDiscounts: {
          coffee: 0,
          spreadsheets: 0,
          budget: 0,
          connections: 0,
          time: 0
        },
        reservedCards: [],
        purchasedCards: [],
        purchasedCardCount: 7,
        claimedExecutives: [],
        claimedExecutiveCount: 0
      },
      {
        gamePlayerId: 3,
        displayName: 'Dwight',
        isHost: false,
        joinStatus: 'connected',
        seatOrder: 3,
        officePrestige: 10,
        resources: {
          coffee: 0,
          spreadsheets: 0,
          budget: 0,
          connections: 0,
          time: 0,
          executiveFavor: 0,
          totalTokens: 0
        },
        permanentDiscounts: {
          coffee: 0,
          spreadsheets: 0,
          budget: 0,
          connections: 0,
          time: 0
        },
        reservedCards: [],
        purchasedCards: [],
        purchasedCardCount: 5,
        claimedExecutives: [],
        claimedExecutiveCount: 0
      }
    ],
    bank: {
      coffee: 4,
      spreadsheets: 4,
      budget: 4,
      connections: 4,
      time: 4,
      executiveFavor: 5
    },
    market: {
      tier1: [],
      tier2: [],
      tier3: []
    },
    executives: [],
    ...overrides
  };
}
