import { TestBed } from '@angular/core/testing';

import { DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
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
    service.patchAvatarDraft({ hair: 'executive-swoop' });

    expect(service.avatarDraft()).toEqual({
      ...DEFAULT_AVATAR_DRAFT,
      hair: 'executive-swoop'
    });
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
      realtime: {
        transport: 'websocket',
        roomSlug: 'synergy-report-telemetry',
        sessionToken: 'temporary-session-token'
      }
    });

    expect(service.stage()).toBe('lobby');
    expect(service.playerName()).toBe('Pam');
    expect(service.currentPlayer()?.isHost).toBeTrue();
    expect(service.joinedPlayers().length).toBe(1);
    expect(localStorage.getItem('watercooler.session.synergy-report-telemetry')).toBe(
      'temporary-session-token'
    );
  });
});
