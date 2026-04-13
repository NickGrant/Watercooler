import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { Router } from '@angular/router';

import { GamesApiService } from '../../core/services/games-api.service';

@Component({
  selector: 'app-home-page',
  imports: [MatButtonModule, MatCardModule],
  templateUrl: './home-page.component.html',
  styleUrl: './home-page.component.scss'
})
export class HomePageComponent {
  private readonly router = inject(Router);
  private readonly gamesApi = inject(GamesApiService);

  readonly createGamePending = signal(false);
  readonly createGameError = signal<string | null>(null);

  createGame(): void {
    if (this.createGamePending()) {
      return;
    }

    this.createGamePending.set(true);
    this.createGameError.set(null);

    this.gamesApi.createGame().subscribe({
      next: async (game) => {
        await this.router.navigate(['/game', game.slug]);
        this.createGamePending.set(false);
      },
      error: (error: unknown) => {
        this.createGamePending.set(false);
        this.createGameError.set('Unable to open a new Watercooler room right now.');
      }
    });
  }
}
