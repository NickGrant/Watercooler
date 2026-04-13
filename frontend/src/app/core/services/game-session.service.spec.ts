import { TestBed } from '@angular/core/testing';

import { DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
import { GameStateResponse } from '../models/game-state-response.model';
import { GameSessionService } from './game-session.service';

describe('GameSessionService', () => {
  let service: GameSessionService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GameSessionService);
    localStorage.clear();
  });

  it('starts with the expected default state', () => {
    expect(service.slug()).toBeNull();
    expect(service.stage()).toBe('pre-join');
    expect(service.stageLabel()).toBe('Awaiting employee identification');
    expect(service.playerName()).toBe('');
    expect(service.avatarDraft()).toEqual(DEFAULT_AVATAR_DRAFT);
  });

  it('updates stage labels when the stage changes', () => {
    service.setStage('lobby');
    expect(service.stageLabel()).toBe('Awaiting executive approval to start');

    service.setStage('in-game');
    expect(service.stageLabel()).toBe('Live workplace maneuvering in progress');
  });

  it('patches avatar data without discarding existing values', () => {
    // BEGIN AGENT CHANGE
    service.patchAvatarDraft({ id: 'avatar-2' });

    expect(service.avatarDraft()).toEqual({
      ...DEFAULT_AVATAR_DRAFT,
      id: 'avatar-2'
    });
    // END AGENT CHANGE
  });

  it('applies accepted join bootstrap state and persists the session token', () => {
    service.applyJoinBootstrap({
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
      transport: {
        transport: 'polling',
        roomSlug: 'synergy-report-telemetry',
        sessionToken: 'temporary-session-token'
      }
    });

    expect(service.stage()).toBe('lobby');
    expect(service.playerName()).toBe('Pam');
    expect(service.currentPlayer()?.isHost).toBeTrue();
    expect(service.joinedPlayers().length).toBe(1);
    expect(service.canRequestStart()).toBeFalse();
    expect(localStorage.getItem('watercooler.session.synergy-report-telemetry')).toBe(
      'temporary-session-token'
    );
  });

  it('reports start readiness for a host once two players are present', () => {
    service.applyJoinBootstrap({
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
      transport: {
        transport: 'polling',
        roomSlug: 'synergy-report-telemetry',
        sessionToken: 'temporary-session-token'
      }
    });

    expect(service.joinedPlayerCount()).toBe(2);
    expect(service.canRequestStart()).toBeTrue();
  });

  it('stores authenticated active state snapshots and locks lobby start actions', () => {
    const state: GameStateResponse = {
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
      transport: {
        transport: 'polling',
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
            officePrestige: 0,
            resources: {
              coffee: 1,
              spreadsheets: 0,
              budget: 0,
              connections: 0,
              time: 0,
              executiveFavor: 0,
              totalTokens: 1
            },
            permanentDiscounts: {
              coffee: 1,
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
          tier1: [],
          tier2: [],
          tier3: []
        },
        executives: []
      }
    };

    service.applyGameState(state);

    expect(service.stage()).toBe('in-game');
    expect(service.activeGameState()?.currentTurnGamePlayerId).toBe(1);
    expect(service.canRequestStart()).toBeFalse();
  });
});
