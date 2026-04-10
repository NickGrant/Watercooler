import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, DestroyRef, inject, signal, ViewEncapsulation } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { ActivatedRoute, Router } from '@angular/router';
import { interval, Observable } from 'rxjs';

import {
  ActiveGameCard,
  ActiveGamePlayer,
  ActiveGameState,
  ActivePlayerCard,
  ResourceLedger,
  ResourceType
} from '../../core/models/active-game-state.model';
import { AvatarDraft } from '../../core/models/avatar-draft.model';
import { GameSummary } from '../../core/models/game-summary.model';
import { GameStateResponse } from '../../core/models/game-state-response.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { StartedGameResponse } from '../../core/models/started-game-response.model';
import { ExecutiveRowComponent } from './components/executive-row/executive-row.component';
import { PlayerCardComponent } from './components/player-card/player-card.component';
import { ResourceBankComponent } from './components/resource-bank/resource-bank.component';
import { VisibleMarketComponent } from './components/visible-market/visible-market.component';

interface ActionRecoveryPayload {
  shouldResync?: boolean;
  game?: GameSummary;
  state?: ActiveGameState;
}

type AvatarPart = keyof AvatarDraft;

interface AvatarOptionDefinition {
  value: string;
  label: string;
  imagePath: string;
}

@Component({
  selector: 'app-game-page',
  imports: [
    FormsModule,
    MatButtonModule,
    MatCardModule,
    MatDividerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSnackBarModule,
    MatTooltipModule,
    PlayerCardComponent,
    ResourceBankComponent,
    VisibleMarketComponent,
    ExecutiveRowComponent
  ],
  templateUrl: './game-page.component.html',
  styleUrl: './game-page.component.scss',
  encapsulation: ViewEncapsulation.None
})
export class GamePageComponent {
  private static readonly STATE_REFRESH_INTERVAL_MS = 2500;

  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly gamesApi = inject(GamesApiService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly snackBar = inject(MatSnackBar);

  readonly session = inject(GameSessionService);
  readonly game = signal<GameSummary | null>(null);
  readonly loadError = signal<string | null>(null);
  readonly joinError = signal<string | null>(null);
  readonly joinPending = signal(false);
  readonly startPending = signal(false);
  readonly startMessage = signal<string | null>(null);
  readonly actionPending = signal(false);
  readonly actionMessage = signal<string | null>(null);
  readonly actionError = signal<string | null>(null);
  readonly latestToastMessage = signal<string | null>(null);
  readonly createNextGamePending = signal(false);
  readonly showRulesHelp = signal(false);
  readonly startedGame = signal<ActiveGameState | null>(null);
  readonly selectedTakeResources = signal<ResourceType[]>([]);
  readonly avatarOptions: Record<AvatarPart, AvatarOptionDefinition[]> = {
    hair: [
      { value: 'side-part', label: 'Side Part', imagePath: 'avatar-options/hair/side-part.svg' },
      { value: 'executive-swoop', label: 'Executive Swoop', imagePath: 'avatar-options/hair/executive-swoop.svg' },
      { value: 'startup-mess', label: 'Startup Mess', imagePath: 'avatar-options/hair/startup-mess.svg' },
      { value: 'weekend-buzz', label: 'Weekend Buzz', imagePath: 'avatar-options/hair/weekend-buzz.svg' },
      { value: 'presentation-curl', label: 'Presentation Curl', imagePath: 'avatar-options/hair/presentation-curl.svg' }
    ],
    face: [
      { value: 'corporate-neutral', label: 'Corporate Neutral', imagePath: 'avatar-options/face/corporate-neutral.svg' },
      { value: 'coffee-grin', label: 'Coffee Grin', imagePath: 'avatar-options/face/coffee-grin.svg' },
      { value: 'meeting-fatigue', label: 'Meeting Fatigue', imagePath: 'avatar-options/face/meeting-fatigue.svg' },
      { value: 'deadline-focus', label: 'Deadline Focus', imagePath: 'avatar-options/face/deadline-focus.svg' },
      { value: 'visionary-smirk', label: 'Visionary Smirk', imagePath: 'avatar-options/face/visionary-smirk.svg' }
    ],
    body: [
      { value: 'blazer', label: 'Blazer', imagePath: 'avatar-options/body/blazer.svg' },
      { value: 'hoodie', label: 'Hoodie', imagePath: 'avatar-options/body/hoodie.svg' },
      { value: 'cardigan', label: 'Cardigan', imagePath: 'avatar-options/body/cardigan.svg' },
      { value: 'polo', label: 'Polo', imagePath: 'avatar-options/body/polo.svg' },
      { value: 'power-suit', label: 'Power Suit', imagePath: 'avatar-options/body/power-suit.svg' }
    ]
  };
  readonly resourceTypes: ResourceType[] = [
    'coffee',
    'spreadsheets',
    'budget',
    'connections',
    'time'
  ];
  readonly formattedRoomName = computed(() =>
    this.formatRoomName(this.game()?.slug ?? this.session.slug())
  );
  readonly activeState = computed(() => this.startedGame());
  readonly orderedPlayers = computed(
    () => [...(this.activeState()?.players ?? [])].sort((left, right) => left.seatOrder - right.seatOrder)
  );
  readonly currentTurnPlayer = computed(
    () =>
      this.activeState()?.players.find(
        (player) => player.gamePlayerId === this.activeState()?.currentTurnGamePlayerId
      ) ?? null
  );
  readonly currentUserPlayer = computed(
    () =>
      this.activeState()?.players.find(
        (player) => player.gamePlayerId === this.session.currentPlayer()?.gamePlayerId
      ) ?? null
  );
  readonly isCurrentPlayersTurn = computed(
    () => this.currentUserPlayer()?.gamePlayerId === this.activeState()?.currentTurnGamePlayerId
  );
  readonly isCompletedGame = computed(
    () => this.game()?.phase === 'completed' || this.game()?.status === 'completed'
  );
  readonly finalStandings = computed(() => {
    const standings = [...(this.activeState()?.players ?? [])];

    return standings.sort((left, right) => {
      const prestigeComparison = right.officePrestige - left.officePrestige;
      if (prestigeComparison !== 0) {
        return prestigeComparison;
      }

      const purchasedCardComparison = left.purchasedCardCount - right.purchasedCardCount;
      if (purchasedCardComparison !== 0) {
        return purchasedCardComparison;
      }

      return left.seatOrder - right.seatOrder;
    });
  });
  readonly winningPlayer = computed(() => this.finalStandings()[0] ?? null);
  readonly tiedPlayers = computed(() => {
    const winner = this.winningPlayer();

    if (winner === null) {
      return [];
    }

    return this.finalStandings().filter(
      (player) =>
        player.officePrestige === winner.officePrestige &&
        player.purchasedCardCount === winner.purchasedCardCount
    );
  });

  constructor() {
    const slug = this.route.snapshot.paramMap.get('slug') ?? 'unknown-room';
    this.session.setSlug(slug);
    this.loadGame(slug);
    this.startStateRefreshLoop();

    const sessionToken = this.session.sessionToken();
    if (sessionToken !== null) {
      this.refreshGameState(sessionToken, {
        target: 'startup',
        successMessage: 'Recovered your saved room state.',
        failureMessage: 'We could not refresh your saved room state.'
      });
    }
  }

  private startStateRefreshLoop(): void {
    interval(GamePageComponent.STATE_REFRESH_INTERVAL_MS)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe(() => {
        this.refreshPassiveState();
      });
  }

  private refreshPassiveState(): void {
    const slug = this.session.slug();
    const sessionToken = this.session.sessionToken();

    if (
      slug === null ||
      sessionToken === null ||
      this.session.stage() === 'pre-join' ||
      this.joinPending() ||
      this.startPending() ||
      this.actionPending()
    ) {
      return;
    }

    this.gamesApi.getGameState(slug, sessionToken).subscribe({
      next: (result) => {
        this.applyGameStateResult(result, 'passive');
      },
      error: (error: unknown) => {
        if (this.getStatus(error) === 401) {
          this.session.clearStoredSessionToken(slug);
          this.joinError.set('Your temporary access badge expired. Please identify yourself again.');
        }
      }
    });
  }

  updatePlayerName(name: string): void {
    this.session.setPlayerName(name);
  }

  updateAvatar(part: keyof AvatarDraft, value: string): void {
    this.session.patchAvatarDraft({ [part]: value });
  }

  cycleAvatarOption(part: AvatarPart, direction: -1 | 1): void {
    const options = this.avatarOptions[part];
    const currentValue = this.session.avatarDraft()[part];
    const currentIndex = Math.max(
      0,
      options.findIndex((option) => option.value === currentValue)
    );
    const nextIndex = (currentIndex + direction + options.length) % options.length;

    this.updateAvatar(part, options[nextIndex].value);
  }

  currentAvatarOption(part: AvatarPart): AvatarOptionDefinition {
    return (
      this.avatarOptions[part].find((option) => option.value === this.session.avatarDraft()[part]) ??
      this.avatarOptions[part][0]
    );
  }

  toggleRulesHelp(): void {
    this.showRulesHelp.set(!this.showRulesHelp());
  }

  submitJoin(): void {
    this.performJoin({
      displayName: this.session.playerName(),
      avatar: this.session.avatarDraft()
    });
  }

  requestStartGame(): void {
    if (!this.session.canRequestStart()) {
      this.startMessage.set('The host can start once at least two employees have checked in.');
      return;
    }

    const slug = this.session.slug();
    const sessionToken = this.session.sessionToken();

    if (slug === null || sessionToken === null || this.startPending()) {
      return;
    }

    this.startPending.set(true);
    this.startMessage.set(null);

    this.gamesApi.startGame(slug, { sessionToken }).subscribe({
      next: (result) => {
        this.applyStartedGameState(result.game, result.state, {
          toastMessage: 'The room is now live. The opening board is ready.'
        });
        this.startPending.set(false);
        this.actionMessage.set(null);
        this.actionError.set(null);
        this.startMessage.set(null);
      },
      error: (error: unknown) => {
        this.startPending.set(false);
        this.startMessage.set(this.getStartErrorMessage(error));
      }
    });
  }

  async createNextGame(): Promise<void> {
    if (this.createNextGamePending()) {
      return;
    }

    this.createNextGamePending.set(true);
    this.actionError.set(null);

    this.gamesApi.createGame().subscribe({
      next: async (game) => {
        this.createNextGamePending.set(false);
        await this.router.navigate(['/game', game.slug]);
      },
      error: (error: unknown) => {
        this.createNextGamePending.set(false);
        this.actionError.set(this.getErrorMessage(error));
      }
    });
  }

  private refreshGameState(
    sessionToken: string,
    options: {
      target: 'startup' | 'action';
      successMessage: string;
      failureMessage: string;
    }
  ): void {
    const slug = this.session.slug();

    if (slug === null) {
      return;
    }

    this.gamesApi.getGameState(slug, sessionToken).subscribe({
      next: (result) => {
        this.applyGameStateResult(
          result,
          options.target === 'startup' ? 'startup' : 'action-recovery'
        );
        this.selectedTakeResources.set([]);

        if (options.target === 'startup') {
          this.startMessage.set(options.successMessage);
        } else {
          this.actionError.set(null);
          this.actionMessage.set(options.successMessage);
        }
      },
      error: (error: unknown) => {
        if (this.getStatus(error) === 401) {
          this.session.clearStoredSessionToken(slug);
          if (options.target === 'startup') {
            this.joinError.set('Your temporary access badge expired. Please identify yourself again.');
          } else {
            this.actionError.set('Your temporary access badge expired. Please identify yourself again.');
            this.joinError.set('Your temporary access badge expired. Please identify yourself again.');
            this.actionMessage.set(null);
          }
          return;
        }

        if (options.target === 'startup') {
          this.joinError.set(options.failureMessage);
        } else {
          this.actionError.set(options.failureMessage);
          this.actionMessage.set(null);
        }
      }
    });
  }

  private loadGame(slug: string): void {
    this.gamesApi.getGame(slug).subscribe({
      next: (game) => {
        this.game.set(game);
      },
      error: (error: unknown) => {
        this.loadError.set(this.getErrorMessage(error));
      }
    });
  }

  private applyGameStateResult(
    result: GameStateResponse,
    source: 'startup' | 'passive' | 'action-recovery'
  ): void {
    if (result.state !== undefined) {
      const stateChangeSummary = this.describeStateChanges(this.startedGame(), result.state);
      this.startedGame.set(result.state);

      if (stateChangeSummary !== null && source === 'passive') {
        this.openStateToast(stateChangeSummary);
      }
    } else {
      this.startedGame.set(null);
    }

    this.session.applyGameState(result);
    this.game.set(result.game);
    this.joinError.set(null);
  }

  private applyStartedGameState(
    game: GameSummary,
    state: ActiveGameState,
    options: { toastMessage?: string } = {}
  ): void {
    const stateChangeSummary = this.describeStateChanges(this.startedGame(), state);

    this.startedGame.set(state);
    this.session.applyStartedGameState(state);
    this.game.set(game);

    const toastMessage = options.toastMessage ?? stateChangeSummary;
    if (toastMessage !== undefined && toastMessage !== null) {
      this.openStateToast(toastMessage);
    }
  }

  private performJoin(payload: { displayName: string; avatar: AvatarDraft } | { sessionToken: string }): void {
    const slug = this.session.slug();

    if (slug === null || this.joinPending()) {
      return;
    }

    this.joinPending.set(true);
    this.joinError.set(null);

    this.gamesApi.joinBootstrap(slug, payload).subscribe({
      next: (result) => {
        this.startedGame.set(null);
        this.session.applyJoinBootstrap(result);
        this.game.set(result.game);
        this.startMessage.set(null);
        this.joinPending.set(false);
      },
      error: (error: unknown) => {
        if ('sessionToken' in payload) {
          this.session.clearStoredSessionToken(slug);
        }

        this.joinPending.set(false);
        this.joinError.set(this.getJoinErrorMessage(error));
      }
    });
  }

  getActivePlayerPrestige(gamePlayerId: number | null | undefined): number {
    if (gamePlayerId === null || gamePlayerId === undefined) {
      return 0;
    }

    return this.startedGame()?.players.find((player) => player.gamePlayerId === gamePlayerId)
      ?.officePrestige ?? 0;
  }

  toggleTakeResource(resource: ResourceType): void {
    const current = [...this.selectedTakeResources()];
    const existingIndex = current.indexOf(resource);

    if (existingIndex >= 0) {
      current.splice(existingIndex, 1);
      this.selectedTakeResources.set(current);
      return;
    }

    if (current.length >= 3) {
      return;
    }

    current.push(resource);
    this.selectedTakeResources.set(current);
  }

  submitTakeResources(): void {
    const sessionToken = this.session.sessionToken();
    const slug = this.session.slug();

    if (sessionToken === null || slug === null || this.selectedTakeResources().length === 0) {
      return;
    }

    this.performGameAction(
      this.gamesApi.takeResources(slug, {
        sessionToken,
        resources: this.selectedTakeResources()
      }),
      'Resources claimed from the office supply bank.'
    );
  }

  claimMarketCard(card: ActiveGameCard): void {
    const sessionToken = this.session.sessionToken();
    const slug = this.session.slug();

    if (sessionToken === null || slug === null) {
      return;
    }

    this.performGameAction(
      this.gamesApi.claimProject(slug, {
        sessionToken,
        source: 'market',
        tier: card.tier,
        marketSlot: card.marketSlot
      }),
      `${card.name} moved into your claimed-project tray.`
    );
  }

  purchaseMarketCard(card: ActiveGameCard): void {
    const sessionToken = this.session.sessionToken();
    const slug = this.session.slug();

    if (sessionToken === null || slug === null) {
      return;
    }

    this.performGameAction(
      this.gamesApi.purchaseAdvantage(slug, {
        sessionToken,
        source: 'market',
        tier: card.tier,
        marketSlot: card.marketSlot
      }),
      `${card.name} is now marked as a completed project.`
    );
  }

  purchaseReservedCard(card: ActivePlayerCard): void {
    const sessionToken = this.session.sessionToken();
    const slug = this.session.slug();

    if (sessionToken === null || slug === null) {
      return;
    }

    this.performGameAction(
      this.gamesApi.purchaseAdvantage(slug, {
        sessionToken,
        source: 'reserved',
        cardCode: card.code
      }),
      `${card.name} was purchased from your claimed-project tray.`
    );
  }

  resourceLabel(resource: string): string {
    return resource === 'executiveFavor'
      ? 'Executive Favor'
      : resource.replace(/([A-Z])/g, ' $1').replace(/^./, (value) => value.toUpperCase());
  }

  resourceIconPath(resource: string): string {
    const iconName = resource === 'executiveFavor' ? 'executive-favor' : resource;

    return `/resources/${iconName}.png`;
  }

  resourceEntries(resources: Record<string, number>): Array<[string, number]> {
    return Object.entries(resources).filter(([, amount]) => amount > 0);
  }

  totalVisibleResources(resources: ResourceLedger): number {
    return resources.totalTokens ?? resources.coffee + resources.spreadsheets + resources.budget + resources.connections + resources.time + resources.executiveFavor;
  }

  canAffordCard(player: ActiveGamePlayer | null, card: ActivePlayerCard | ActiveGameCard): boolean {
    if (player === null) {
      return false;
    }

    let remainingExecutiveFavor = player.resources.executiveFavor;

    return this.resourceEntries(card.cost).every(([resource, amount]) => {
      const permanentDiscount = player.permanentDiscounts[resource as ResourceType] ?? 0;
      const discountedCost = Math.max(0, amount - permanentDiscount);
      const available = player.resources[resource as keyof ResourceLedger];

      if (typeof available !== 'number') {
        return false;
      }

      if (available >= discountedCost) {
        return true;
      }

      const shortfall = discountedCost - available;

      if (shortfall > remainingExecutiveFavor) {
        return false;
      }

      remainingExecutiveFavor -= shortfall;
      return true;
    });
  }

  trackByCardCode(_index: number, card: ActiveGameCard | ActivePlayerCard): string {
    return card.code;
  }

  finalPlacementLabel(index: number): string {
    return ['1st', '2nd', '3rd', '4th'][index] ?? `${index + 1}th`;
  }

  finalTieBreakSummary(): string {
    const winner = this.winningPlayer();

    if (winner === null) {
      return 'The final standings are unavailable.';
    }

    if (this.tiedPlayers().length > 1) {
      return `${winner.displayName} won the tie on seat order after prestige and purchased-card count remained tied.`;
    }

    const runnerUp = this.finalStandings()[1] ?? null;
    if (runnerUp !== null && runnerUp.officePrestige === winner.officePrestige) {
      return `${winner.displayName} won the tie-break by finishing with fewer completed projects.`;
    }

    return `${winner.displayName} secured the win on Office Prestige.`;
  }

  formatRoomName(slug: string | null | undefined): string {
    if (typeof slug !== 'string' || slug.trim() === '') {
      return 'Unknown Room';
    }

    return slug
      .split('-')
      .filter((segment) => segment.trim().length > 0)
      .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
      .join(' ');
  }

  private performGameAction(
    request$: Observable<StartedGameResponse>,
    successMessage: string
  ): void {
    if (this.actionPending()) {
      return;
    }

    this.actionPending.set(true);
    this.actionMessage.set(null);
    this.actionError.set(null);

    request$.subscribe({
      next: (result) => {
        this.applyStartedGameState(result.game, result.state, {
          toastMessage: this.describeStateChanges(this.startedGame(), result.state) ?? successMessage
        });
        this.selectedTakeResources.set([]);
        this.actionPending.set(false);
        this.actionMessage.set(null);
      },
      error: (error: unknown) => {
        this.actionPending.set(false);
        const recovery = this.extractActionRecovery(error);

        if (recovery !== null) {
          this.applyActionRecovery(recovery);
          return;
        }

        const status = this.getStatus(error);
        const sessionToken = this.session.sessionToken();

        if (status === 401) {
          const slug = this.session.slug();

          if (slug !== null) {
            this.session.clearStoredSessionToken(slug);
          }

          this.actionError.set('Your temporary access badge expired. Please identify yourself again.');
          this.actionMessage.set(null);
          return;
        }

        if (sessionToken !== null && [0, 409, 412, 423].includes(status)) {
          this.actionMessage.set(
            'The board may have drifted. Refreshing the latest room state now...'
          );
          this.refreshGameState(sessionToken, {
            target: 'action',
            successMessage: 'Recovered the latest room state after a stale action.',
            failureMessage: 'The board could not be refreshed after that action.'
          });
          return;
        }

        this.actionError.set(this.getActionErrorMessage(error));
      }
    });
  }

  private applyActionRecovery(recovery: ActionRecoveryPayload): void {
    if (recovery.state !== undefined) {
      this.startedGame.set(recovery.state);
      this.session.applyStartedGameState(recovery.state);
    }

    if (recovery.game !== undefined) {
      this.game.set(recovery.game);
    }

    this.selectedTakeResources.set([]);
    this.actionMessage.set(null);
    this.actionError.set(null);
    this.openStateToast('A newer room state was detected and your board was resynced.');
  }

  private describeStateChanges(previous: ActiveGameState | null, next: ActiveGameState): string | null {
    if (previous === null) {
      return null;
    }

    const events: string[] = [];

    for (const player of next.players) {
      const previousPlayer = previous.players.find(
        (candidate) => candidate.gamePlayerId === player.gamePlayerId
      );

      if (previousPlayer === undefined) {
        continue;
      }

      const newPurchasedCard = player.purchasedCards.find(
        (card) => !previousPlayer.purchasedCards.some((candidate) => candidate.code === card.code)
      );
      if (newPurchasedCard !== undefined) {
        events.push(`${player.displayName} acquired ${newPurchasedCard.name}.`);
      }

      const newReservedCard = player.reservedCards.find(
        (card) => !previousPlayer.reservedCards.some((candidate) => candidate.code === card.code)
      );
      if (newReservedCard !== undefined) {
        events.push(`${player.displayName} claimed ${newReservedCard.name}.`);
      }

      const newExecutive = player.claimedExecutives.find(
        (executive) =>
          !previousPlayer.claimedExecutives.some((candidate) => candidate.code === executive.code)
      );
      if (newExecutive !== undefined) {
        events.push(`${player.displayName} secured ${newExecutive.name}.`);
      }
    }

    if (previous.currentTurnGamePlayerId !== next.currentTurnGamePlayerId) {
      const activePlayer = next.players.find(
        (player) => player.gamePlayerId === next.currentTurnGamePlayerId
      );

      if (activePlayer !== undefined) {
        const currentPlayerId = this.session.currentPlayer()?.gamePlayerId;
        events.push(
          activePlayer.gamePlayerId === currentPlayerId
            ? 'It is now your turn.'
            : `It is now ${activePlayer.displayName}'s turn.`
        );
      }
    }

    return events.length > 0 ? events.slice(0, 2).join(' ') : null;
  }

  private openStateToast(message: string): void {
    this.latestToastMessage.set(message);
    this.snackBar.dismiss();
    this.snackBar.open(message, 'Dismiss', {
      duration: 4200,
      horizontalPosition: 'center',
      verticalPosition: 'bottom'
    });
  }

  private getErrorMessage(error: unknown): string {
    const status = this.getStatus(error);

    if (status === 404) {
      return 'This room slug does not map to an active Watercooler game.';
    }

    return 'Game details are temporarily unavailable.';
  }

  private getJoinErrorMessage(error: unknown): string {
    const status = this.getStatus(error);
    const apiMessage =
      error instanceof HttpErrorResponse
        ? error.error?.message
        : typeof error === 'object' && error !== null && 'error' in error
          ? (error.error as { message?: unknown }).message
          : null;

    if (typeof apiMessage === 'string') {
      return apiMessage;
    }

    if (status === 401) {
      return 'Your temporary access badge expired. Please identify yourself again.';
    }

    return 'The Recreation Division could not process that join request.';
  }

  private getStartErrorMessage(error: unknown): string {
    const apiMessage = this.getApiMessage(error);

    if (typeof apiMessage === 'string') {
      return apiMessage;
    }

    return 'The host start request could not be completed.';
  }

  private getActionErrorMessage(error: unknown): string {
    const apiMessage = this.getApiMessage(error);
    const status = this.getStatus(error);

    if (typeof apiMessage === 'string') {
      return apiMessage;
    }

    if (status === 401) {
      return 'Your temporary access badge expired. Please identify yourself again.';
    }

    if (status === 409) {
      return 'That action is stale. The board needs to resync before you can try again.';
    }

    if (status === 0) {
      return 'The office action could not complete because the network connection dropped.';
    }

    return 'The office action could not be completed. Please resync and try again.';
  }

  private extractActionRecovery(error: unknown): ActionRecoveryPayload | null {
    const payload = this.getErrorPayload(error);
    const recovery = payload !== null && typeof payload === 'object' && 'recovery' in payload
      ? (payload.recovery as ActionRecoveryPayload | null)
      : null;

    if (recovery === null) {
      return null;
    }

    if (recovery.state === undefined && recovery.game === undefined) {
      return null;
    }

    return recovery;
  }

  private getApiMessage(error: unknown): string | null {
    const payload = this.getErrorPayload(error);

    return payload !== null && typeof payload.message === 'string' ? payload.message : null;
  }

  private getErrorPayload(error: unknown): {
    message?: unknown;
    recovery?: unknown;
  } | null {
    if (error instanceof HttpErrorResponse) {
      return typeof error.error === 'object' && error.error !== null ? error.error : null;
    }

    if (typeof error === 'object' && error !== null && 'error' in error) {
      const payload = (error as { error?: unknown }).error;
      return typeof payload === 'object' && payload !== null ? (payload as { message?: unknown; recovery?: unknown }) : null;
    }

    return null;
  }

  private getStatus(error: unknown): number {
    return error instanceof HttpErrorResponse
      ? error.status
      : typeof error === 'object' && error !== null && 'status' in error
        ? Number(error.status)
        : 0;
  }
}
