import { AvatarDraft } from './avatar-draft.model';

export interface JoinedPlayer {
  gamePlayerId: number;
  playerId: number;
  displayName: string;
  isHost: boolean;
  joinStatus: string;
  avatar: AvatarDraft;
}
