import { TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { of, throwError } from 'rxjs';

import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { GamePageComponent } from './game-page.component';

describe('GamePageComponent', () => {
  let gamesApi: jasmine.SpyObj<GamesApiService>;

  beforeEach(async () => {
    gamesApi = jasmine.createSpyObj<GamesApiService>('GamesApiService', ['getGame']);
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

  it('updates the session stage when a shell state is selected', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    fixture.componentInstance.setStage('lobby');
    expect(session.stage()).toBe('lobby');
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
});
