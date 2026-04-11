import { ActiveGameState } from './active-game-state.model';
import { GameSummary } from './game-summary.model';
import { JoinedPlayer } from './joined-player.model';

export interface GameStateResponse {
  game: GameSummary;
  player: JoinedPlayer;
  session: {
    token: string;
    reconnectEnabled: boolean;
  };
  transport: {
    transport: string;
    roomSlug: string;
    sessionToken: string;
  };
  lobby?: {
    minimumPlayers: number;
    maximumPlayers: number;
    canStart: boolean;
    joinedPlayers: JoinedPlayer[];
  };
  state?: ActiveGameState;
}
