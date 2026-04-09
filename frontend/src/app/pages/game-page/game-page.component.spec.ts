import { TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { of, throwError } from 'rxjs';

import { ActiveGameState } from '../../core/models/active-game-state.model';
import { DEFAULT_AVATAR_DRAFT } from '../../core/models/avatar-draft.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { GamePageComponent } from './game-page.component';

describe('GamePageComponent', () => {
  let gamesApi: jasmine.SpyObj<GamesApiService>;

  beforeEach(async () => {
    localStorage.clear();
    gamesApi = jasmine.createSpyObj<GamesApiService>('GamesApiService', [
      'getGame',
      'getGameState',
      'joinBootstrap',
      'startGame',
      'takeResources',
      'claimProject',
      'purchaseAdvantage'
    ]);
    gamesApi.getGame.and.returnValue(
      of({
        id: 1,
        slug: 'synergy-report-telemetry',
        status: 'lobby',
        phase: 'pre_join',
        playerCount: 0,
        createdAt: '2026-04-08 00:00:00',
        path: '/game/synergy-report-telemetry'
      })
    );
    gamesApi.joinBootstrap.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'lobby',
          phase: 'lobby',
          playerCount: 1,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        player: {
          gamePlayerId: 1,
          playerId: 1,
          displayName: 'Pam',
          isHost: true,
          joinStatus: 'joined',
          avatar: DEFAULT_AVATAR_DRAFT
        },
        session: {
          token: 'temporary-session-token',
          reconnectEnabled: true
        },
        lobby: {
          minimumPlayers: 2,
          maximumPlayers: 4,
          canStart: false,
          joinedPlayers: [
            {
              gamePlayerId: 1,
              playerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'joined',
              avatar: DEFAULT_AVATAR_DRAFT
            }
          ]
        },
        realtime: {
          transport: 'websocket',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        }
      })
    );
    gamesApi.getGameState.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'active',
          phase: 'active',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        player: {
          gamePlayerId: 1,
          playerId: 1,
          displayName: 'Pam',
          isHost: true,
          joinStatus: 'connected',
          avatar: DEFAULT_AVATAR_DRAFT
        },
        session: {
          token: 'temporary-session-token',
          reconnectEnabled: true
        },
        realtime: {
          transport: 'websocket',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        },
        state: createActiveState()
      })
    );
    gamesApi.startGame.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'active',
          phase: 'active',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        state: createActiveState()
      })
    );
    gamesApi.takeResources.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'active',
          phase: 'active',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        state: createActiveState({
          currentTurnGamePlayerId: 2,
          bank: {
            coffee: 3,
            spreadsheets: 4,
            budget: 3,
            connections: 4,
            time: 3,
            executiveFavor: 5
          }
        })
      })
    );
    gamesApi.claimProject.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'active',
          phase: 'active',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        state: createActiveState({
          currentTurnGamePlayerId: 2,
          players: [
            {
              ...createActiveState().players[0],
              resources: {
                coffee: 1,
                spreadsheets: 1,
                budget: 1,
                connections: 0,
                time: 0,
                executiveFavor: 1,
                totalTokens: 4
              },
              reservedCards: [
                {
                  code: 'reserved-1',
                  tier: 1,
                  name: 'Conference Room Coup',
                  resourceDiscount: 'coffee',
                  officePrestige: 0,
                  cost: {
                    coffee: 0,
                    spreadsheets: 1,
                    budget: 1,
                    connections: 1,
                    time: 0
                  }
                }
              ],
              purchasedCardCount: 0,
              claimedExecutiveCount: 0
            },
            createActiveState().players[1]
          ],
          bank: {
            coffee: 4,
            spreadsheets: 4,
            budget: 4,
            connections: 4,
            time: 4,
            executiveFavor: 4
          }
        })
      })
    );
    gamesApi.purchaseAdvantage.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'active',
          phase: 'active',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        state: createActiveState({
          currentTurnGamePlayerId: 2,
          players: [
            {
              ...createActiveState().players[0],
              officePrestige: 1,
              resources: {
                coffee: 0,
                spreadsheets: 1,
                budget: 1,
                connections: 0,
                time: 0,
                executiveFavor: 0,
                totalTokens: 2
              },
              permanentDiscounts: {
                coffee: 1,
                spreadsheets: 0,
                budget: 0,
                connections: 0,
                time: 0
              },
              reservedCards: [],
              purchasedCards: [
                {
                  code: 'market-card-1',
                  tier: 1,
                  name: 'Budget Buffer',
                  resourceDiscount: 'coffee',
                  officePrestige: 1,
                  cost: {
                    coffee: 2,
                    spreadsheets: 0,
                    budget: 1,
                    connections: 0,
                    time: 0
                  }
                }
              ],
              purchasedCardCount: 1,
              claimedExecutives: [],
              claimedExecutiveCount: 0
            },
            createActiveState().players[1]
          ]
        })
      })
    );

    await TestBed.configureTestingModule({
      imports: [GamePageComponent],
      providers: [
        GameSessionService,
        { provide: GamesApiService, useValue: gamesApi },
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              paramMap: convertToParamMap({ slug: 'synergy-report-telemetry' })
            }
          }
        }
      ]
    }).compileComponents();
  });

  it('loads the route slug into the shared session service', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    expect(session.slug()).toBe('synergy-report-telemetry');
    expect(fixture.componentInstance.session).toBe(session);
    expect(gamesApi.getGame).toHaveBeenCalledWith('synergy-report-telemetry');
  });

  it('updates the player name in shared state', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    fixture.componentInstance.updatePlayerName('Pam');
    expect(session.playerName()).toBe('Pam');
  });

  it('stores the loaded game summary for the route', () => {
    const fixture = TestBed.createComponent(GamePageComponent);

    expect(fixture.componentInstance.game()?.slug).toBe('synergy-report-telemetry');
  });

  it('restores an active game from the authenticated state endpoint when a session token exists', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    expect(gamesApi.getGameState).toHaveBeenCalledWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
    expect(gamesApi.joinBootstrap).not.toHaveBeenCalled();
    expect(session.stage()).toBe('in-game');
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(1);
    expect(fixture.componentInstance.startMessage()).toContain('authenticated state endpoint');
  });

  it('restores lobby state from the authenticated state endpoint when a session token exists', () => {
    gamesApi.getGameState.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'lobby',
          phase: 'lobby',
          playerCount: 1,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        player: {
          gamePlayerId: 1,
          playerId: 1,
          displayName: 'Pam',
          isHost: true,
          joinStatus: 'joined',
          avatar: DEFAULT_AVATAR_DRAFT
        },
        session: {
          token: 'temporary-session-token',
          reconnectEnabled: true
        },
        realtime: {
          transport: 'websocket',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        },
        lobby: {
          minimumPlayers: 2,
          maximumPlayers: 4,
          canStart: false,
          joinedPlayers: [
            {
              gamePlayerId: 1,
              playerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'joined',
              avatar: DEFAULT_AVATAR_DRAFT
            }
          ]
        }
      })
    );
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    expect(gamesApi.getGameState).toHaveBeenCalledWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
    expect(gamesApi.joinBootstrap).not.toHaveBeenCalled();
    expect(session.stage()).toBe('lobby');
    expect(session.joinedPlayers().length).toBe(1);
    expect(fixture.componentInstance.startedGame()).toBeNull();
  });

  it('shows a room-specific error when the game lookup returns 404', () => {
    gamesApi.getGame.and.returnValue(throwError(() => ({ status: 404 })));

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.componentInstance.loadError()).toBe(
      'This room slug does not map to an active Watercooler game.'
    );
  });

  it('submits join details and applies the accepted lobby state', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    fixture.componentInstance.updatePlayerName('Pam');
    fixture.componentInstance.submitJoin();

    expect(gamesApi.joinBootstrap).toHaveBeenCalledWith('synergy-report-telemetry', {
      displayName: 'Pam',
      avatar: DEFAULT_AVATAR_DRAFT
    });
    expect(session.stage()).toBe('lobby');
    expect(session.currentPlayer()?.displayName).toBe('Pam');
  });

  it('shows backend join errors to the player', () => {
    gamesApi.joinBootstrap.and.returnValue(
      throwError(() => ({
        status: 409,
        error: {
          message: 'Display names must be unique within the game.'
        }
      }))
    );

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.updatePlayerName('Pam');
    fixture.componentInstance.submitJoin();

    expect(fixture.componentInstance.joinError()).toBe(
      'Display names must be unique within the game.'
    );
  });

  it('starts the game and moves the shell into active play once the roster is large enough', () => {
    gamesApi.joinBootstrap.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'lobby',
          phase: 'lobby',
          playerCount: 2,
          createdAt: '2026-04-08 00:00:00',
          path: '/game/synergy-report-telemetry'
        },
        player: {
          gamePlayerId: 1,
          playerId: 1,
          displayName: 'Pam',
          isHost: true,
          joinStatus: 'connected',
          avatar: DEFAULT_AVATAR_DRAFT
        },
        session: {
          token: 'temporary-session-token',
          reconnectEnabled: true
        },
        lobby: {
          minimumPlayers: 2,
          maximumPlayers: 4,
          canStart: true,
          joinedPlayers: [
            {
              gamePlayerId: 1,
              playerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'connected',
              avatar: DEFAULT_AVATAR_DRAFT
            },
            {
              gamePlayerId: 2,
              playerId: 2,
              displayName: 'Jim',
              isHost: false,
              joinStatus: 'joined',
              avatar: DEFAULT_AVATAR_DRAFT
            }
          ]
        },
        realtime: {
          transport: 'websocket',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        }
      })
    );

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.updatePlayerName('Pam');
    fixture.componentInstance.submitJoin();
    fixture.componentInstance.requestStartGame();

    expect(gamesApi.startGame).toHaveBeenCalledWith('synergy-report-telemetry', {
      sessionToken: 'temporary-session-token'
    });
    expect(fixture.componentInstance.session.stage()).toBe('in-game');
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(1);
    expect(fixture.componentInstance.startMessage()).toContain('synchronized opening state');
  });

  it('submits take-resource actions against the active game API', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.toggleTakeResource('coffee');
    fixture.componentInstance.toggleTakeResource('budget');
    fixture.componentInstance.toggleTakeResource('time');
    fixture.componentInstance.submitTakeResources();

    expect(gamesApi.takeResources).toHaveBeenCalledWith('synergy-report-telemetry', {
      sessionToken: 'temporary-session-token',
      resources: ['coffee', 'budget', 'time']
    });
    expect(fixture.componentInstance.actionMessage()).toContain('office supply bank');
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(2);
  });

  it('claims a market card through the active game API', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    const card = fixture.componentInstance.startedGame()?.market.tier1[0];

    expect(card).toBeDefined();

    fixture.componentInstance.claimMarketCard(card!);

    expect(gamesApi.claimProject).toHaveBeenCalledWith('synergy-report-telemetry', {
      sessionToken: 'temporary-session-token',
      source: 'market',
      tier: 1,
      marketSlot: 1
    });
    expect(fixture.componentInstance.actionMessage()).toContain('claimed-project tray');
  });

  it('purchases a reserved card through the active game API', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.claimMarketCard(fixture.componentInstance.startedGame()!.market.tier1[0]);

    const reservedCard = fixture.componentInstance.startedGame()?.players[0].reservedCards[0];

    expect(reservedCard).toBeDefined();

    fixture.componentInstance.purchaseReservedCard(reservedCard!);

    expect(gamesApi.purchaseAdvantage).toHaveBeenCalledWith('synergy-report-telemetry', {
      sessionToken: 'temporary-session-token',
      source: 'reserved',
      cardCode: 'reserved-1'
    });
    expect(fixture.componentInstance.actionMessage()).toContain('claimed-project tray');
  });
});

function createActiveState(overrides: Partial<ActiveGameState> = {}): ActiveGameState {
  const defaultState: ActiveGameState = {
    currentTurnGamePlayerId: 1,
    players: [
      {
        gamePlayerId: 1,
        displayName: 'Pam',
        isHost: true,
        joinStatus: 'connected',
        seatOrder: 1,
        officePrestige: 0,
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
        purchasedCardCount: 0,
        claimedExecutives: [],
        claimedExecutiveCount: 0
      },
      {
        gamePlayerId: 2,
        displayName: 'Jim',
        isHost: false,
        joinStatus: 'connected',
        seatOrder: 2,
        officePrestige: 0,
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
        purchasedCardCount: 0,
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
      tier1: [
        {
          code: 'market-card-1',
          tier: 1,
          name: 'Budget Buffer',
          resourceDiscount: 'coffee',
          officePrestige: 1,
          cost: {
            coffee: 2,
            spreadsheets: 0,
            budget: 1,
            connections: 0,
            time: 0
          },
          marketSlot: 1
        }
      ],
      tier2: [],
      tier3: []
    },
    executives: [
      {
        code: 'vp-of-synergy',
        name: 'VP of Synergy',
        officePrestige: 3,
        requirements: {
          coffee: 3,
          spreadsheets: 0,
          budget: 3,
          connections: 3,
          time: 0
        },
        slotOrder: 1
      }
    ]
  };

  return {
    ...defaultState,
    ...overrides
  };
}
