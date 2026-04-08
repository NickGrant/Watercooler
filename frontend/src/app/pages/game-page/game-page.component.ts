import { Component, inject } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { ActivatedRoute } from '@angular/router';

import { GameSessionService } from '../../core/services/game-session.service';

@Component({
  selector: 'app-game-page',
  imports: [MatButtonModule, MatCardModule, MatDividerModule],
  templateUrl: './game-page.component.html',
  styleUrl: './game-page.component.scss'
})
export class GamePageComponent {
  private readonly route = inject(ActivatedRoute);

  readonly session = inject(GameSessionService);

  constructor() {
    this.session.setSlug(this.route.snapshot.paramMap.get('slug') ?? 'unknown-room');
  }

  setStage(stage: 'pre-join' | 'lobby' | 'in-game'): void {
    this.session.setStage(stage);
  }
}
