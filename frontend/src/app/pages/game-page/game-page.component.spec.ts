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
    gamesApi = jasmine.createSpyObj<GamesApiService>('GamesApiService', ['getGame', 'joinBootstrap']);
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
});
