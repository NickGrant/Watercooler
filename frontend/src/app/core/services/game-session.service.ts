import { computed, Injectable, signal } from '@angular/core';

import { AvatarDraft, DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
import { GameShellStage } from '../models/game-shell-stage.model';
import { JoinBootstrapResponse } from '../models/join-bootstrap-response.model';
import { JoinedPlayer } from '../models/joined-player.model';

@Injectable({
  providedIn: 'root'
})
export class GameSessionService {
  private static readonly STORAGE_PREFIX = 'watercooler.session.';

  private readonly slugState = signal<string | null>(null);
  private readonly stageState = signal<GameShellStage>('pre-join');
  private readonly playerNameState = signal('');
  private readonly avatarDraftState = signal<AvatarDraft>(DEFAULT_AVATAR_DRAFT);
  private readonly sessionTokenState = signal<string | null>(null);
  private readonly currentPlayerState = signal<JoinedPlayer | null>(null);
  private readonly joinedPlayersState = signal<JoinedPlayer[]>([]);

  readonly slug = this.slugState.asReadonly();
  readonly stage = this.stageState.asReadonly();
  readonly playerName = this.playerNameState.asReadonly();
  readonly avatarDraft = this.avatarDraftState.asReadonly();
  readonly sessionToken = this.sessionTokenState.asReadonly();
  readonly currentPlayer = this.currentPlayerState.asReadonly();
  readonly joinedPlayers = this.joinedPlayersState.asReadonly();
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
  readonly hasJoined = computed(() => this.currentPlayerState() !== null);

  setSlug(slug: string): void {
    if (this.slugState() !== slug) {
      this.slugState.set(slug);
      this.stageState.set('pre-join');
      this.sessionTokenState.set(this.readStoredSessionToken(slug));
      this.currentPlayerState.set(null);
      this.joinedPlayersState.set([]);
    }
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

  applyJoinBootstrap(result: JoinBootstrapResponse): void {
    this.slugState.set(result.game.slug);
    this.stageState.set('lobby');
    this.playerNameState.set(result.player.displayName);
    this.avatarDraftState.set(result.player.avatar);
    this.sessionTokenState.set(result.session.token);
    this.currentPlayerState.set(result.player);
    this.joinedPlayersState.set(result.lobby.joinedPlayers);
    this.persistSessionToken(result.game.slug, result.session.token);
  }

  clearStoredSessionToken(slug: string): void {
    localStorage.removeItem(this.storageKey(slug));
    if (this.slugState() === slug) {
      this.sessionTokenState.set(null);
    }
  }

  private persistSessionToken(slug: string, token: string): void {
    localStorage.setItem(this.storageKey(slug), token);
  }

  private readStoredSessionToken(slug: string): string | null {
    return localStorage.getItem(this.storageKey(slug));
  }

  private storageKey(slug: string): string {
    return `${GameSessionService.STORAGE_PREFIX}${slug}`;
  }
}
