import { HttpErrorResponse } from '@angular/common/http';
import { Component, computed, DestroyRef, inject, signal, ViewEncapsulation } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { ActivatedRoute, Router } from '@angular/router';
import { Observable } from 'rxjs';

import {
  ActiveGameCard,
  ActiveGamePlayer,
  ActiveGameState,
  ActivePlayerCard,
  ResourceLedger,
  ResourceType
} from '../../core/models/active-game-state.model';
import {
  AVATAR_OPTIONS,
  AvatarOptionDefinition,
  AvatarPart,
  resolveAvatarOption
} from '../../core/avatar/avatar-options';
import { AvatarDraft } from '../../core/models/avatar-draft.model';
import { GameSummary } from '../../core/models/game-summary.model';
import { GameStateResponse } from '../../core/models/game-state-response.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { StartedGameResponse } from '../../core/models/started-game-response.model';
import { AvatarCompositeComponent } from '../../shared/components/avatar-composite/avatar-composite.component';
import {
  buildFinalTieBreakSummary as buildFinalTieBreakSummaryText,
  canAffordCard as canAffordCardWithResources,
  describeStateChanges as describeGameStateChanges,
  finalPlacementLabel as formatFinalPlacementLabel,
  formatRoomName as formatGameRoomName,
  isExecutiveRequirementMet as isExecutiveRequirementSatisfied,
  resourceEntries as collectResourceEntries,
  resourceIconPath as buildResourceIconPath,
  resourceLabel as formatResourceLabel,
  sortPlayersByFinalStanding,
  totalVisibleResources as countVisibleResources
} from './game-page-state.utils';
import { ExecutiveRowComponent } from './components/executive-row/executive-row.component';
import { PlayerCardComponent } from './components/player-card/player-card.component';
import { ResourceBankComponent } from './components/resource-bank/resource-bank.component';
import { VisibleMarketComponent } from './components/visible-market/visible-market.component';

interface ActionRecoveryPayload {
  shouldResync?: boolean;
  game?: GameSummary;
  state?: ActiveGameState;
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
    AvatarCompositeComponent,
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
  private static readonly LOBBY_REFRESH_INTERVAL_MS = 3000;
  private static readonly ACTIVE_TURN_REFRESH_INTERVAL_MS = 1500;
  private static readonly ACTIVE_WAIT_REFRESH_INTERVAL_MS = 2500;
  private static readonly HIDDEN_REFRESH_INTERVAL_MS = 12000;
  private static readonly COMPLETED_REFRESH_INTERVAL_MS = 15000;

  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);
  private readonly gamesApi = inject(GamesApiService);
  private readonly destroyRef = inject(DestroyRef);
  private readonly snackBar = inject(MatSnackBar);
  private passiveRefreshTimeoutId: ReturnType<typeof setTimeout> | null = null;
  private passiveRefreshPending = false;
  private readonly handleVisibilityChange = (): void => {
    if (this.isDocumentHidden()) {
      this.scheduleNextPassiveRefresh();
      return;
    }

    this.runPassiveRefreshCycle(0);
  };

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
  readonly showBugReportPanel = signal(false);
  readonly bugReportPending = signal(false);
  readonly bugReportError = signal<string | null>(null);
  readonly bugReportSuccess = signal<string | null>(null);
  readonly bugReportReplyEmail = signal('');
  readonly bugReportMessage = signal('');
  readonly startedGame = signal<ActiveGameState | null>(null);
  readonly selectedTakeResources = signal<ResourceType[]>([]);
  readonly avatarOptions: Record<AvatarPart, AvatarOptionDefinition[]> = AVATAR_OPTIONS;
  readonly resourceTypes: ResourceType[] = [
    'coffee',
    'spreadsheets',
    'budget',
    'connections',
    'time'
  ];
  readonly formattedRoomName = computed(() =>
    formatGameRoomName(this.game()?.slug ?? this.session.slug())
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
    return sortPlayersByFinalStanding(this.activeState()?.players ?? []);
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
    globalThis.document?.addEventListener('visibilitychange', this.handleVisibilityChange);
    this.destroyRef.onDestroy(() => {
      if (this.passiveRefreshTimeoutId !== null) {
        clearTimeout(this.passiveRefreshTimeoutId);
      }

      globalThis.document?.removeEventListener('visibilitychange', this.handleVisibilityChange);
    });
    this.scheduleNextPassiveRefresh();
  }

  private runPassiveRefreshCycle(delayMs = this.currentRefreshIntervalMs()): void {
    if (this.passiveRefreshPending) {
      this.scheduleNextPassiveRefresh(delayMs);
      return;
    }

    if (delayMs > 0) {
      this.scheduleNextPassiveRefresh(delayMs);
      return;
    }

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
      this.scheduleNextPassiveRefresh();
      return;
    }

    this.passiveRefreshPending = true;
    this.gamesApi.getGameState(slug, sessionToken).subscribe({
      next: (result) => {
        this.passiveRefreshPending = false;
        this.applyGameStateResult(result, 'passive');
        this.scheduleNextPassiveRefresh();
      },
      error: (error: unknown) => {
        this.passiveRefreshPending = false;
        if (this.getStatus(error) === 401) {
          this.session.clearStoredSessionToken(slug);
          this.joinError.set('Your temporary access badge expired. Please identify yourself again.');
        }

        this.scheduleNextPassiveRefresh();
      }
    });
  }

  private scheduleNextPassiveRefresh(delayMs = this.currentRefreshIntervalMs()): void {
    if (this.passiveRefreshTimeoutId !== null) {
      clearTimeout(this.passiveRefreshTimeoutId);
    }

    this.passiveRefreshTimeoutId = setTimeout(() => {
      this.passiveRefreshTimeoutId = null;
      this.runPassiveRefreshCycle(0);
    }, delayMs);
  }

  private currentRefreshIntervalMs(): number {
    if (this.isDocumentHidden()) {
      return GamePageComponent.HIDDEN_REFRESH_INTERVAL_MS;
    }

    if (this.isCompletedGame()) {
      return GamePageComponent.COMPLETED_REFRESH_INTERVAL_MS;
    }

    if (this.session.stage() === 'lobby') {
      return GamePageComponent.LOBBY_REFRESH_INTERVAL_MS;
    }

    if (this.session.stage() === 'in-game') {
      return this.isCurrentPlayersTurn()
        ? GamePageComponent.ACTIVE_TURN_REFRESH_INTERVAL_MS
        : GamePageComponent.ACTIVE_WAIT_REFRESH_INTERVAL_MS;
    }

    return GamePageComponent.LOBBY_REFRESH_INTERVAL_MS;
  }

  private isDocumentHidden(): boolean {
    return globalThis.document?.visibilityState === 'hidden';
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
    return resolveAvatarOption(part, this.session.avatarDraft()[part]);
  }

  avatarOptionLabel(part: AvatarPart, value: string): string {
    return resolveAvatarOption(part, value).label;
  }

  toggleRulesHelp(): void {
    this.showRulesHelp.set(!this.showRulesHelp());
  }

  toggleBugReportPanel(): void {
    this.showBugReportPanel.update((currentValue) => !currentValue);
    this.bugReportError.set(null);
    this.bugReportSuccess.set(null);
  }

  updateBugReportReplyEmail(value: string): void {
    this.bugReportReplyEmail.set(value);
  }

  updateBugReportMessage(value: string): void {
    this.bugReportMessage.set(value);
  }

  submitBugReport(): void {
    const slug = this.session.slug();
    const sessionToken = this.session.sessionToken();

    if (slug === null || sessionToken === null) {
      this.bugReportError.set('Check in to the room before submitting a linked bug report.');
      return;
    }

    if (this.bugReportPending()) {
      return;
    }

    this.bugReportPending.set(true);
    this.bugReportError.set(null);
    this.bugReportSuccess.set(null);

    const replyEmail = this.bugReportReplyEmail().trim();

    this.gamesApi
      .submitBugReport(slug, {
        sessionToken,
        message: this.bugReportMessage(),
        ...(replyEmail !== '' ? { replyEmail } : {})
      })
      .subscribe({
        next: () => {
          this.bugReportPending.set(false);
          this.bugReportMessage.set('');
          this.bugReportReplyEmail.set('');
          this.bugReportSuccess.set(
            'Bug report submitted. The Recreation Division logged it for review.'
          );
        },
        error: (error: unknown) => {
          this.bugReportPending.set(false);
          this.bugReportError.set(this.getBugReportErrorMessage(error));
        }
      });
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
      const stateChangeSummary = describeGameStateChanges(
        this.startedGame(),
        result.state,
        this.session.currentPlayer()?.gamePlayerId
      );
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
    this.scheduleNextPassiveRefresh();
  }

  private applyStartedGameState(
    game: GameSummary,
    state: ActiveGameState,
    options: { toastMessage?: string } = {}
  ): void {
    const stateChangeSummary = describeGameStateChanges(
      this.startedGame(),
      state,
      this.session.currentPlayer()?.gamePlayerId
    );

    this.startedGame.set(state);
    this.session.applyStartedGameState(state);
    this.game.set(game);
    this.scheduleNextPassiveRefresh();

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
        this.scheduleNextPassiveRefresh();
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
    return formatResourceLabel(resource);
  }

  resourceIconPath(resource: string): string {
    return buildResourceIconPath(resource);
  }

  resourceEntries(resources: Record<string, number>): Array<[string, number]> {
    return collectResourceEntries(resources);
  }

  totalVisibleResources(resources: ResourceLedger): number {
    return countVisibleResources(resources);
  }

  isExecutiveRequirementMet(resource: string, amount: number): boolean {
    return isExecutiveRequirementSatisfied(this.currentUserPlayer(), resource, amount);
  }

  canAffordCard(player: ActiveGamePlayer | null, card: ActivePlayerCard | ActiveGameCard): boolean {
    return canAffordCardWithResources(player, card);
  }

  trackByCardCode(_index: number, card: ActiveGameCard | ActivePlayerCard): string {
    return card.code;
  }

  finalPlacementLabel(index: number): string {
    return formatFinalPlacementLabel(index);
  }

  finalTieBreakSummary(): string {
    return buildFinalTieBreakSummaryText(this.finalStandings());
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
          toastMessage: describeGameStateChanges(
            this.startedGame(),
            result.state,
            this.session.currentPlayer()?.gamePlayerId
          ) ?? successMessage
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

  private getBugReportErrorMessage(error: unknown): string {
    const apiMessage = this.getApiMessage(error);

    if (typeof apiMessage === 'string') {
      return apiMessage;
    }

    if (this.getStatus(error) === 401) {
      return 'Your temporary access badge expired. Please identify yourself again.';
    }

    return 'The bug report could not be submitted right now.';
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
