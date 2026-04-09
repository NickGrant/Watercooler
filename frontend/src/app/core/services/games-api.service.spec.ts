import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
import { GameSummary } from '../models/game-summary.model';
import { GameStateResponse } from '../models/game-state-response.model';
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

  it('submits join-bootstrap requests for a slug', () => {
    let response: unknown;

    service
      .joinBootstrap('synergy-report-telemetry', {
        displayName: 'Pam',
        avatar: DEFAULT_AVATAR_DRAFT
      })
      .subscribe((result) => {
        response = result;
      });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry/join-bootstrap');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({
      displayName: 'Pam',
      avatar: DEFAULT_AVATAR_DRAFT
    });

    request.flush({
      game: sampleGame,
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
        joinedPlayers: []
      },
      realtime: {
        transport: 'websocket',
        roomSlug: 'synergy-report-telemetry',
        sessionToken: 'temporary-session-token'
      }
    });

    expect(response).toBeTruthy();
  });

  it('loads authenticated game state through the API', () => {
    let response: GameStateResponse | undefined;

    service.getGameState('synergy-report-telemetry', 'temporary-session-token').subscribe((state) => {
      response = state;
    });

    const request = httpController.expectOne(
      '/api/games/synergy-report-telemetry/state?sessionToken=temporary-session-token'
    );
    expect(request.request.method).toBe('GET');

    request.flush({
      game: sampleGame,
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
        players: [],
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
    });

    expect(response?.game.slug).toBe('synergy-report-telemetry');
    expect(response?.state?.currentTurnGamePlayerId).toBe(1);
  });

  it('submits start-game requests for a slug', () => {
    let response: unknown;

    service
      .startGame('synergy-report-telemetry', {
        sessionToken: 'temporary-session-token'
      })
      .subscribe((result) => {
        response = result;
      });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry/start');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({
      sessionToken: 'temporary-session-token'
    });

    request.flush({
      game: {
        ...sampleGame,
        status: 'active',
        phase: 'active',
        playerCount: 2
      },
      state: {
        currentTurnGamePlayerId: 1,
        players: [],
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
    });

    expect(response).toBeTruthy();
  });

  it('submits take-resource requests for a slug', () => {
    let response: unknown;

    service
      .takeResources('synergy-report-telemetry', {
        sessionToken: 'temporary-session-token',
        resources: ['coffee', 'budget', 'time']
      })
      .subscribe((result) => {
        response = result;
      });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry/take-resources');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({
      sessionToken: 'temporary-session-token',
      resources: ['coffee', 'budget', 'time']
    });

    request.flush({
      game: {
        ...sampleGame,
        status: 'active',
        phase: 'active',
        playerCount: 2
      },
      state: {
        currentTurnGamePlayerId: 2,
        players: [],
        bank: {
          coffee: 3,
          spreadsheets: 4,
          budget: 3,
          connections: 4,
          time: 3,
          executiveFavor: 5
        },
        market: {
          tier1: [],
          tier2: [],
          tier3: []
        },
        executives: []
      }
    });

    expect(response).toBeTruthy();
  });

  it('submits claim-project requests for a market card', () => {
    let response: unknown;

    service
      .claimProject('synergy-report-telemetry', {
        sessionToken: 'temporary-session-token',
        source: 'market',
        tier: 2,
        marketSlot: 3
      })
      .subscribe((result) => {
        response = result;
      });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry/claim-project');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({
      sessionToken: 'temporary-session-token',
      source: 'market',
      tier: 2,
      marketSlot: 3
    });

    request.flush({
      game: {
        ...sampleGame,
        status: 'active',
        phase: 'active',
        playerCount: 2
      },
      state: {
        currentTurnGamePlayerId: 2,
        players: [],
        bank: {
          coffee: 4,
          spreadsheets: 4,
          budget: 4,
          connections: 4,
          time: 4,
          executiveFavor: 4
        },
        market: {
          tier1: [],
          tier2: [],
          tier3: []
        },
        executives: []
      }
    });

    expect(response).toBeTruthy();
  });

  it('submits purchase requests for a reserved card', () => {
    let response: unknown;

    service
      .purchaseAdvantage('synergy-report-telemetry', {
        sessionToken: 'temporary-session-token',
        source: 'reserved',
        cardCode: 'reserved-1'
      })
      .subscribe((result) => {
        response = result;
      });

    const request = httpController.expectOne('/api/games/synergy-report-telemetry/purchase-advantage');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual({
      sessionToken: 'temporary-session-token',
      source: 'reserved',
      cardCode: 'reserved-1'
    });

    request.flush({
      game: {
        ...sampleGame,
        status: 'active',
        phase: 'active',
        playerCount: 2
      },
      state: {
        currentTurnGamePlayerId: 2,
        players: [],
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
    });

    expect(response).toBeTruthy();
  });
});
