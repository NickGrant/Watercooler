import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { ActivatedRoute } from '@angular/router';

import { AvatarDraft } from '../../core/models/avatar-draft.model';
import { GameSummary } from '../../core/models/game-summary.model';
import { GameStateResponse } from '../../core/models/game-state-response.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';
import { StartedGameResponse } from '../../core/models/started-game-response.model';

@Component({
  selector: 'app-game-page',
  imports: [
    FormsModule,
    MatButtonModule,
    MatCardModule,
    MatDividerModule,
    MatFormFieldModule,
    MatInputModule,
    MatSelectModule
  ],
  templateUrl: './game-page.component.html',
  styleUrl: './game-page.component.scss'
})
export class GamePageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly gamesApi = inject(GamesApiService);

  readonly session = inject(GameSessionService);
  readonly game = signal<GameSummary | null>(null);
  readonly loadError = signal<string | null>(null);
  readonly joinError = signal<string | null>(null);
  readonly joinPending = signal(false);
  readonly startPending = signal(false);
  readonly startMessage = signal<string | null>(null);
  readonly startedGame = signal<StartedGameResponse['state'] | null>(null);
  readonly bodyOptions = ['blazer', 'hoodie', 'cardigan', 'polo', 'power-suit'];
  readonly faceOptions = [
    'corporate-neutral',
    'coffee-grin',
    'meeting-fatigue',
    'deadline-focus',
    'visionary-smirk'
  ];
  readonly hairOptions = [
    'side-part',
    'executive-swoop',
    'startup-mess',
    'weekend-buzz',
    'presentation-curl'
  ];

  constructor() {
    const slug = this.route.snapshot.paramMap.get('slug') ?? 'unknown-room';
    this.session.setSlug(slug);
    this.loadGame(slug);

    const sessionToken = this.session.sessionToken();
    if (sessionToken !== null) {
      this.syncGameState(sessionToken);
    }
  }

  updatePlayerName(name: string): void {
    this.session.setPlayerName(name);
  }

  updateAvatar(part: keyof AvatarDraft, value: string): void {
    this.session.patchAvatarDraft({ [part]: value });
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
        this.startedGame.set(result.state);
        this.session.applyStartedGameState(result.state);
        this.game.set(result.game);
        this.startPending.set(false);
        this.startMessage.set('The room is now live. The synchronized opening state came from the API.');
      },
      error: (error: unknown) => {
        this.startPending.set(false);
        this.startMessage.set(this.getStartErrorMessage(error));
      }
    });
  }

  private syncGameState(sessionToken: string): void {
    const slug = this.session.slug();

    if (slug === null) {
      return;
    }

    this.gamesApi.getGameState(slug, sessionToken).subscribe({
      next: (result) => {
        this.applyGameStateResult(result);
      },
      error: (error: unknown) => {
        if (this.getStatus(error) === 401) {
          this.session.clearStoredSessionToken(slug);
          this.joinError.set('Your temporary access badge expired. Please identify yourself again.');
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

  private applyGameStateResult(result: GameStateResponse): void {
    this.startedGame.set(result.state ?? null);
    this.session.applyGameState(result);
    this.game.set(result.game);
    this.joinError.set(null);

    if (result.state !== undefined) {
      this.startMessage.set('Recovered synchronized game state from the authenticated state endpoint.');
    } else {
      this.startMessage.set(null);
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
    const apiMessage =
      error instanceof HttpErrorResponse
        ? error.error?.message
        : typeof error === 'object' && error !== null && 'error' in error
          ? (error.error as { message?: unknown }).message
          : null;

    if (typeof apiMessage === 'string') {
      return apiMessage;
    }

    return 'The host start request could not be completed.';
  }

  private getStatus(error: unknown): number {
    return error instanceof HttpErrorResponse
      ? error.status
      : typeof error === 'object' && error !== null && 'status' in error
        ? Number(error.status)
        : 0;
  }
}
