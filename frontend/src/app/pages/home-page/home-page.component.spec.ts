import { TestBed } from '@angular/core/testing';
import { provideRouter, Router } from '@angular/router';
import { of, throwError } from 'rxjs';

import { GamesApiService } from '../../core/services/games-api.service';
import { HomePageComponent } from './home-page.component';

describe('HomePageComponent', () => {
  let gamesApi: jasmine.SpyObj<GamesApiService>;

  beforeEach(async () => {
    gamesApi = jasmine.createSpyObj<GamesApiService>('GamesApiService', ['createGame']);

    await TestBed.configureTestingModule({
      imports: [HomePageComponent],
      providers: [provideRouter([]), { provide: GamesApiService, useValue: gamesApi }]
    }).compileComponents();
  });

  it('exposes the preview slug used by the route shell button', () => {
    const fixture = TestBed.createComponent(HomePageComponent);
    expect(fixture.componentInstance.sampleSlug).toBe('synergy-report-telemetry');
  });

  it('renders the route preview button', () => {
    const fixture = TestBed.createComponent(HomePageComponent);
    fixture.detectChanges();

    const link = fixture.nativeElement.querySelector('a[mat-flat-button]');
    expect(link?.textContent).toContain('Preview Game Route Shell');
  });

  it('creates a game and navigates to the game route', async () => {
    gamesApi.createGame.and.returnValue(
      of({
        id: 7,
        slug: 'synergy-report-telemetry',
        status: 'lobby',
        phase: 'pre_join',
        playerCount: 0,
        createdAt: '2026-04-08 00:00:00',
        path: '/game/synergy-report-telemetry'
      })
    );

    const fixture = TestBed.createComponent(HomePageComponent);
    const router = TestBed.inject(Router);
    const navigateSpy = spyOn(router, 'navigate').and.resolveTo(true);

    fixture.componentInstance.createGame();

    expect(gamesApi.createGame).toHaveBeenCalled();
    expect(navigateSpy).toHaveBeenCalledWith(['/game', 'synergy-report-telemetry']);
  });

  it('shows an error banner when game creation fails', () => {
    gamesApi.createGame.and.returnValue(throwError(() => new Error('network')));

    const fixture = TestBed.createComponent(HomePageComponent);
    fixture.detectChanges();

    fixture.componentInstance.createGame();
    fixture.detectChanges();

    const errorBanner = fixture.nativeElement.querySelector('[role="alert"]');
    expect(errorBanner?.textContent).toContain('Unable to open a new Watercooler room right now.');
  });
});
