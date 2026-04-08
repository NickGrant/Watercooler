import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { ActivatedRoute } from '@angular/router';

import { GameSummary } from '../../core/models/game-summary.model';
import { GamesApiService } from '../../core/services/games-api.service';
import { GameSessionService } from '../../core/services/game-session.service';

@Component({
  selector: 'app-game-page',
  imports: [MatButtonModule, MatCardModule, MatDividerModule],
  templateUrl: './game-page.component.html',
  styleUrl: './game-page.component.scss'
})
export class GamePageComponent {
  private readonly route = inject(ActivatedRoute);
  private readonly gamesApi = inject(GamesApiService);

  readonly session = inject(GameSessionService);
  readonly game = signal<GameSummary | null>(null);
  readonly loadError = signal<string | null>(null);

  constructor() {
    const slug = this.route.snapshot.paramMap.get('slug') ?? 'unknown-room';
    this.session.setSlug(slug);

    this.gamesApi.getGame(slug).subscribe({
      next: (game) => {
        this.game.set(game);
      },
      error: (error: unknown) => {
        this.loadError.set(this.getErrorMessage(error));
      }
    });
  }

  setStage(stage: 'pre-join' | 'lobby' | 'in-game'): void {
    this.session.setStage(stage);
  }

  private getErrorMessage(error: unknown): string {
    const status =
      error instanceof HttpErrorResponse
        ? error.status
        : typeof error === 'object' && error !== null && 'status' in error
          ? Number(error.status)
          : 0;

    if (status === 404) {
      return 'This room slug does not map to an active Watercooler game.';
    }

    return 'Game details are temporarily unavailable.';
  }
}
