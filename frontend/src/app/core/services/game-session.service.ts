import { computed, Injectable, signal } from '@angular/core';

import { AvatarDraft, DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
import { GameShellStage } from '../models/game-shell-stage.model';

@Injectable({
  providedIn: 'root'
})
export class GameSessionService {
  private readonly slugState = signal<string | null>(null);
  private readonly stageState = signal<GameShellStage>('pre-join');
  private readonly playerNameState = signal('');
  private readonly avatarDraftState = signal<AvatarDraft>(DEFAULT_AVATAR_DRAFT);

  readonly slug = this.slugState.asReadonly();
  readonly stage = this.stageState.asReadonly();
  readonly playerName = this.playerNameState.asReadonly();
  readonly avatarDraft = this.avatarDraftState.asReadonly();
  readonly stageLabel = computed(() => {
    switch (this.stageState()) {
      case 'pre-join':
        return 'Awaiting employee identification';
      case 'lobby':
        return 'Awaiting executive approval to start';
      case 'in-game':
        return 'Live workplace maneuvering in progress';
    }
  });

  setSlug(slug: string): void {
    this.slugState.set(slug);
  }

  setStage(stage: GameShellStage): void {
    this.stageState.set(stage);
  }

  setPlayerName(name: string): void {
    this.playerNameState.set(name);
  }

  patchAvatarDraft(patch: Partial<AvatarDraft>): void {
    this.avatarDraftState.update((current) => ({ ...current, ...patch }));
  }
}
