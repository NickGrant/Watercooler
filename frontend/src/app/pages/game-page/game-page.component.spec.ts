import { TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { of, throwError } from 'rxjs';

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
      'startGame'
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
        state: {
          currentTurnGamePlayerId: 1,
          players: [
            {
              gamePlayerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'connected',
              seatOrder: 1,
              officePrestige: 0
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
          executives: []
        }
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
        state: {
          currentTurnGamePlayerId: 1,
          players: [
            {
              gamePlayerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'connected',
              seatOrder: 1,
              officePrestige: 0
            },
            {
              gamePlayerId: 2,
              displayName: 'Jim',
              isHost: false,
              joinStatus: 'joined',
              seatOrder: 2,
              officePrestige: 0
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
          executives: []
        }
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
});
