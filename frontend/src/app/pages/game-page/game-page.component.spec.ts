import { fakeAsync, TestBed, tick } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';
import { Router } from '@angular/router';
import { of, throwError } from 'rxjs';

import { ActiveGameState } from '../../core/models/active-game-state.model';
import { DEFAULT_AVATAR_DRAFT } from '../../core/models/avatar-draft.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { GamePageComponent } from './game-page.component';

describe('GamePageComponent', () => {
  let gamesApi: jasmine.SpyObj<GamesApiService>;
  let router: jasmine.SpyObj<Router>;
  let originalVisibilityStateDescriptor: PropertyDescriptor | undefined;
  let originalHiddenDescriptor: PropertyDescriptor | undefined;

  beforeEach(async () => {
    localStorage.clear();
    originalVisibilityStateDescriptor = Object.getOwnPropertyDescriptor(document, 'visibilityState');
    originalHiddenDescriptor = Object.getOwnPropertyDescriptor(document, 'hidden');
    setDocumentVisibility(false);
    gamesApi = jasmine.createSpyObj<GamesApiService>('GamesApiService', [
      'createGame',
      'getGame',
      'getGameState',
      'joinBootstrap',
      'startGame',
      'takeResources',
      'claimProject',
      'purchaseAdvantage',
      'submitBugReport'
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
    gamesApi.createGame.and.returnValue(
      of({
        id: 2,
        slug: 'new-room-slug',
        status: 'lobby',
        phase: 'pre_join',
        playerCount: 0,
        createdAt: '2026-04-08 00:05:00',
        path: '/game/new-room-slug'
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
          transport: 'polling',
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
          transport: 'polling',
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
    gamesApi.submitBugReport.and.returnValue(
      of({
        id: 7,
        status: 'unread',
        createdAt: '2026-04-10 23:45:00'
      })
    );
    router = jasmine.createSpyObj<Router>('Router', ['navigate']);
    router.navigate.and.returnValue(Promise.resolve(true));
    await TestBed.configureTestingModule({
      imports: [GamePageComponent],
      providers: [
        GameSessionService,
        { provide: GamesApiService, useValue: gamesApi },
        { provide: Router, useValue: router },
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

  afterEach(() => {
    restoreDocumentProperty('visibilityState', originalVisibilityStateDescriptor);
    restoreDocumentProperty('hidden', originalHiddenDescriptor);
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

  it('renders the avatar carousels in hair, face, body order', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    const labels = Array.from(
      fixture.nativeElement.querySelectorAll('.avatar-carousel__header span'),
      (element: Element) => element.textContent?.trim()
    );

    expect(labels).toEqual(['Hair', 'Face', 'Body']);
  });

  it('renders the compact bank row and stacked player economy bars', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    const resourceBankTiles = fixture.nativeElement.querySelectorAll('.resource-bank__tile');
    const resourceBankTile = fixture.nativeElement.querySelector('.resource-bank__tile--selectable');
    const firstPlayerPanel = fixture.nativeElement.querySelector('.player-panel');
    const economyBars = firstPlayerPanel?.querySelectorAll('.player-resource-bar') ?? [];
    const favorChip = firstPlayerPanel?.querySelector('.player-favor-chip');
    const resourceCost = fixture.nativeElement.querySelector('.resource-cost');

    expect(resourceBankTiles.length).toBe(6);
    expect(resourceBankTile?.getAttribute('aria-label')).toContain('Coffee');
    expect(resourceBankTile?.textContent?.replace(/\s+/g, '')).toContain('4Coffee');
    expect(resourceBankTile?.querySelector('app-resource-icon')).not.toBeNull();
    expect(resourceBankTile?.querySelector('.resource-icon-badge--large')).not.toBeNull();
    expect(economyBars.length).toBe(5);
    expect(economyBars[0]?.getAttribute('aria-label')).toContain('Coffee');
    expect(economyBars[0]?.querySelector('.player-resource-bar__frame')).not.toBeNull();
    expect(favorChip?.textContent?.replace(/\s+/g, '')).toContain('ExecutiveFavor0');
    expect(resourceCost?.getAttribute('aria-label')).toContain('Coffee');
    expect(resourceCost?.textContent?.replace(/\s+/g, '')).toBe('2');
  });

  it('renders the current player card without the extra desk wrapper chrome', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    const sidebarColumn = fixture.nativeElement.querySelector('.shell-column--sidebar');
    const currentPlayerCard = sidebarColumn?.querySelector('app-player-card');

    expect(fixture.nativeElement.textContent).not.toContain('Your Desk');
    expect(fixture.nativeElement.querySelector('[aria-label="Your desk help"]')).toBeNull();
    expect(sidebarColumn?.querySelector('.shell-panel--sidebar-placeholder')).toBeNull();
    expect(currentPlayerCard).not.toBeNull();
    expect(currentPlayerCard?.parentElement).toBe(sidebarColumn);
  });

  it('highlights the current-turn tile instead of rendering a separate your-turn box', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    const highlightedTile = fixture.nativeElement.querySelector('.status-card--turn-active');

    expect(highlightedTile?.textContent).toContain('Current Turn');
    expect(fixture.nativeElement.textContent).not.toContain('Your Turn');
    expect(fixture.nativeElement.textContent).not.toContain('Turn Command Desk');
  });

  it('cycles avatar selections through the image carousel controls', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    fixture.componentInstance.cycleAvatarOption('hair', 1);
    fixture.componentInstance.cycleAvatarOption('face', 1);
    fixture.componentInstance.cycleAvatarOption('body', 1);

    expect(session.avatarDraft()).toEqual({
      hair: 'executive-swoop',
      face: 'coffee-grin',
      body: 'hoodie'
    });
  });

  it('toggles the rules and help desk open and closed', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.componentInstance.showRulesHelp()).toBeFalse();
    expect(fixture.nativeElement.textContent).not.toContain('Turn Options');

    fixture.componentInstance.toggleRulesHelp();
    fixture.detectChanges();

    expect(fixture.componentInstance.showRulesHelp()).toBeTrue();
    expect(fixture.nativeElement.textContent).toContain('Turn Options');
    expect(fixture.nativeElement.textContent).toContain('Final standings break ties');
  });

  it('uses a two-column waiting-room shell without a room snapshot panel', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelectorAll('.shell-panel').length).toBe(2);
    expect(fixture.nativeElement.querySelector('.shell-grid--lobby')).not.toBeNull();
    expect(fixture.nativeElement.querySelector('.shell-panel--check-in')).not.toBeNull();
    expect(fixture.nativeElement.querySelector('.shell-panel--waiting-room')).not.toBeNull();
    expect(fixture.nativeElement.querySelector('.board-panel')).toBeNull();
    expect(fixture.nativeElement.textContent).toContain('Employee Check-In');
    expect(fixture.nativeElement.textContent).toContain('Waiting Room');
  });

  it('opens the floating bug-report panel from the right-edge tab', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.componentInstance.showBugReportPanel()).toBeFalse();
    expect(fixture.nativeElement.querySelector('#bug-report-panel')).toBeNull();

    fixture.componentInstance.toggleBugReportPanel();
    fixture.detectChanges();

    expect(fixture.componentInstance.showBugReportPanel()).toBeTrue();
    expect(fixture.nativeElement.querySelector('#bug-report-panel')).not.toBeNull();
    expect(fixture.nativeElement.textContent).toContain('Report A Bug');
  });

  it('submits a bug report for the current room session', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    fixture.componentInstance.updateBugReportReplyEmail('pam@example.com');
    fixture.componentInstance.updateBugReportMessage(
      'The waiting room briefly showed the wrong player count.'
    );
    fixture.componentInstance.submitBugReport();
    fixture.detectChanges();

    expect(gamesApi.submitBugReport).toHaveBeenCalledWith('synergy-report-telemetry', {
      sessionToken: 'temporary-session-token',
      replyEmail: 'pam@example.com',
      message: 'The waiting room briefly showed the wrong player count.'
    });
    expect(fixture.componentInstance.bugReportMessage()).toBe('');
    expect(fixture.componentInstance.bugReportReplyEmail()).toBe('');
    expect(fixture.componentInstance.bugReportSuccess()).toContain('Bug report submitted');
  });

  it('renders executive portrait cards with a portrait-and-profile layout', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    const executiveCards = fixture.nativeElement.querySelectorAll('.executive-card');

    expect(executiveCards.length).toBe(1);
    expect(executiveCards[0].querySelector('.executive-card__portrait')).not.toBeNull();
    expect(executiveCards[0].querySelector('.executive-card__details')).not.toBeNull();
    expect(executiveCards[0].querySelector('.executive-card__nameplate')).not.toBeNull();
    expect(executiveCards[0].querySelector('.executive-card__prestige')).not.toBeNull();
    expect(executiveCards[0].textContent).toContain('Requirement Profile');
  });

  it('does not show development-oriented board copy on the route shell', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).not.toContain('Authenticated Game State');
    expect(fixture.nativeElement.textContent).not.toContain('Game Route Shell');
  });

  it('stores the loaded game summary for the route', () => {
    const fixture = TestBed.createComponent(GamePageComponent);

    expect(fixture.componentInstance.game()?.slug).toBe('synergy-report-telemetry');
  });

  it('formats the room slug as a readable room title', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.detectChanges();

    expect(fixture.componentInstance.formattedRoomName()).toBe('Synergy Report Telemetry');
    expect(fixture.nativeElement.querySelector('h1')?.textContent?.trim()).toBe(
      'Synergy Report Telemetry'
    );
  });

  it('auto-refreshes the waiting room roster while the lobby is open', fakeAsync(() => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    session.applyJoinBootstrap({
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
        canStart: true,
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
        transport: 'polling',
        roomSlug: 'synergy-report-telemetry',
        sessionToken: 'temporary-session-token'
      }
    });
    gamesApi.getGameState.calls.reset();
    gamesApi.getGameState.and.returnValue(
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
          canStart: true,
          joinedPlayers: [
            {
              gamePlayerId: 1,
              playerId: 1,
              displayName: 'Pam',
              isHost: true,
              joinStatus: 'joined',
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
          transport: 'polling',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        }
      })
    );

    tick(3000);
    fixture.detectChanges();

    expect(gamesApi.getGameState).toHaveBeenCalledOnceWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
    expect(session.joinedPlayerCount()).toBe(2);
    expect(fixture.nativeElement.textContent).toContain('Jim');
  }));

  it('auto-refreshes the active board and announces important state changes', fakeAsync(() => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    const updatedState = createActiveState({
      currentTurnGamePlayerId: 1,
      players: [
        {
          ...createActiveState().players[0],
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
        createActiveState().players[1]
      ]
    });

    gamesApi.getGameState.calls.reset();
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
          transport: 'polling',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        },
        state: updatedState
      })
    );

    tick(2500);
    fixture.detectChanges();

    expect(gamesApi.getGameState).toHaveBeenCalledOnceWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
    expect(fixture.componentInstance.startedGame()?.players[0].purchasedCardCount).toBe(1);
    expect(fixture.componentInstance.latestToastMessage()).toBe('Pam acquired Budget Sign-off.');
  }));

  it('uses a slower polling interval while the tab is hidden', fakeAsync(() => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    gamesApi.getGameState.calls.reset();

    setDocumentVisibility(true);
    document.dispatchEvent(new Event('visibilitychange'));
    tick(3000);
    fixture.detectChanges();

    expect(gamesApi.getGameState).not.toHaveBeenCalled();

    tick(9000);
    fixture.detectChanges();

    expect(gamesApi.getGameState).toHaveBeenCalledOnceWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
  }));

  it('refreshes immediately when the tab becomes visible again', fakeAsync(() => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    gamesApi.getGameState.calls.reset();

    setDocumentVisibility(true);
    document.dispatchEvent(new Event('visibilitychange'));
    gamesApi.getGameState.calls.reset();

    setDocumentVisibility(false);
    document.dispatchEvent(new Event('visibilitychange'));
    tick();
    fixture.detectChanges();

    expect(gamesApi.getGameState).toHaveBeenCalledOnceWith(
      'synergy-report-telemetry',
      'temporary-session-token'
    );
  }));

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
    expect(fixture.componentInstance.startMessage()).toContain('saved room state');
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
          transport: 'polling',
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
          transport: 'polling',
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
    expect(fixture.componentInstance.latestToastMessage()).toBe(
      'The room is now live. The opening board is ready.'
    );
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
    expect(fixture.componentInstance.actionMessage()).toBeNull();
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(2);
    expect(fixture.componentInstance.latestToastMessage()).toBe("It is now Jim's turn.");
  });

  it('uses the embedded recovery payload when an action returns a stale conflict', () => {
    gamesApi.takeResources.and.returnValue(
      throwError(() => ({
        status: 409,
        error: {
          message: 'Only the active player may take resources right now.',
          recovery: {
            shouldResync: true,
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
              currentTurnGamePlayerId: 2
            })
          }
        }
      }))
    );
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.toggleTakeResource('coffee');
    fixture.componentInstance.toggleTakeResource('budget');
    fixture.componentInstance.toggleTakeResource('time');
    fixture.componentInstance.submitTakeResources();

    expect(gamesApi.getGameState.calls.count()).toBe(1);
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(2);
    expect(fixture.componentInstance.actionMessage()).toBeNull();
    expect(fixture.componentInstance.actionError()).toBeNull();
    expect(fixture.componentInstance.latestToastMessage()).toBe(
      'A newer room state was detected and your board was resynced.'
    );
  });

  it('refreshes the board from /state after a stale or network action failure', () => {
    gamesApi.takeResources.and.returnValue(throwError(() => ({ status: 0 })));
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');

    const fixture = TestBed.createComponent(GamePageComponent);
    const refreshedState = createActiveState({
      currentTurnGamePlayerId: 2,
      bank: {
        coffee: 3,
        spreadsheets: 4,
        budget: 3,
        connections: 4,
        time: 3,
        executiveFavor: 5
      }
    });
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
          transport: 'polling',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        },
        state: refreshedState
      })
    );
    fixture.componentInstance.toggleTakeResource('coffee');
    fixture.componentInstance.toggleTakeResource('budget');
    fixture.componentInstance.toggleTakeResource('time');
    fixture.componentInstance.submitTakeResources();

    expect(gamesApi.getGameState.calls.count()).toBe(2);
    expect(fixture.componentInstance.startedGame()?.currentTurnGamePlayerId).toBe(2);
    expect(fixture.componentInstance.actionMessage()).toContain('Recovered the latest room state');
    expect(fixture.componentInstance.actionError()).toBeNull();
  });

  it('marks the session stale when an action refresh also returns 401', () => {
    gamesApi.takeResources.and.returnValue(throwError(() => ({ status: 401 })));
    gamesApi.getGameState.and.returnValue(throwError(() => ({ status: 401 })));

    const fixture = TestBed.createComponent(GamePageComponent);
    fixture.componentInstance.updatePlayerName('Pam');
    fixture.componentInstance.submitJoin();
    fixture.componentInstance.requestStartGame();

    fixture.componentInstance.toggleTakeResource('coffee');
    fixture.componentInstance.toggleTakeResource('budget');
    fixture.componentInstance.toggleTakeResource('time');
    fixture.componentInstance.submitTakeResources();

    expect(fixture.componentInstance.session.sessionToken()).toBeNull();
    expect(fixture.componentInstance.actionError()).toBe(
      'Your temporary access badge expired. Please identify yourself again.'
    );
    expect(localStorage.getItem('watercooler.session.synergy-report-telemetry')).toBeNull();
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
    expect(fixture.componentInstance.latestToastMessage()).toContain('Conference Room Coup');
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
    expect(fixture.componentInstance.latestToastMessage()).toContain('Budget Buffer');
  });

  it('orders completed-game standings by prestige, purchased cards, and seat order', () => {
    localStorage.setItem('watercooler.session.synergy-report-telemetry', 'temporary-session-token');
    gamesApi.getGameState.and.returnValue(
      of({
        game: {
          id: 1,
          slug: 'synergy-report-telemetry',
          status: 'completed',
          phase: 'completed',
          playerCount: 3,
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
          transport: 'polling',
          roomSlug: 'synergy-report-telemetry',
          sessionToken: 'temporary-session-token'
        },
        state: createCompletedState()
      })
    );

    const fixture = TestBed.createComponent(GamePageComponent);
    const standings = fixture.componentInstance.finalStandings();

    expect(fixture.componentInstance.isCompletedGame()).toBeTrue();
    expect(standings.map((player) => player.displayName)).toEqual(['Pam', 'Jim', 'Dwight']);
    expect(fixture.componentInstance.winningPlayer()?.displayName).toBe('Pam');
    expect(fixture.componentInstance.finalTieBreakSummary()).toContain('fewer completed projects');
  });

  it('creates a fresh room from the completed-game results desk', async () => {
    const fixture = TestBed.createComponent(GamePageComponent);

    await fixture.componentInstance.createNextGame();

    expect(gamesApi.createGame).toHaveBeenCalled();
    expect(router.navigate).toHaveBeenCalledWith(['/game', 'new-room-slug']);
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

function createCompletedState(): ActiveGameState {
  return {
    ...createActiveState({
      currentTurnGamePlayerId: 2,
      players: [
        {
          ...createActiveState().players[0],
          displayName: 'Pam',
          seatOrder: 1,
          officePrestige: 12,
          purchasedCardCount: 6
        },
        {
          ...createActiveState().players[1],
          displayName: 'Jim',
          seatOrder: 2,
          officePrestige: 12,
          purchasedCardCount: 7
        },
        {
          ...createActiveState().players[1],
          gamePlayerId: 3,
          displayName: 'Dwight',
          seatOrder: 3,
          officePrestige: 10,
          purchasedCardCount: 5
        }
      ]
    })
  };
}

function setDocumentVisibility(hidden: boolean): void {
  Object.defineProperty(document, 'visibilityState', {
    configurable: true,
    value: hidden ? 'hidden' : 'visible'
  });
  Object.defineProperty(document, 'hidden', {
    configurable: true,
    value: hidden
  });
}

function restoreDocumentProperty(
  propertyName: 'visibilityState' | 'hidden',
  descriptor: PropertyDescriptor | undefined
): void {
  if (descriptor === undefined) {
    delete (document as unknown as Record<string, unknown>)[propertyName];
    return;
  }

  Object.defineProperty(document, propertyName, descriptor);
}
