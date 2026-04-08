import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { GameSummary } from '../models/game-summary.model';
import { GamesApiService } from './games-api.service';

describe('GamesApiService', () => {
  let service: GamesApiService;
  let httpController: HttpTestingController;

  const sampleGame: GameSummary = {
    id: 7,
    slug: 'synergy-report-telemetry',
    status: 'lobby',
    phase: 'pre_join',
    playerCount: 0,
    createdAt: '2026-04-08 00:00:00',
    path: '/game/synergy-report-telemetry'
  };

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()]
    });

    service = TestBed.inject(GamesApiService);
    httpController = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpController.verify();
  });

  it('creates a game through the API', () => {
    let response: GameSummary | undefined;

    service.createGame().subscribe((game) => {
      response = game;
    });

    const request = httpController.expectOne('/api/games');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({});

    request.flush({ game: sampleGame });

    expect(response).toEqual(sampleGame);
  });

  it('loads a game by slug through the API', () => {
    let response: GameSummary | undefined;

    service.getGame('synergy-report-telemetry').subscribe((game) => {
      response = game;
    });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry');
    expect(request.request.method).toBe('GET');

    request.flush({ game: sampleGame });

    expect(response).toEqual(sampleGame);
  });
});
