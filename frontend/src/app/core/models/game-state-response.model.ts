import { GameSummary } from './game-summary.model';
import { JoinedPlayer } from './joined-player.model';
import { StartedGameResponse } from './started-game-response.model';

export interface GameStateResponse {
  game: GameSummary;
  player: JoinedPlayer;
  session: {
    token: string;
    reconnectEnabled: boolean;
  };
  realtime: {
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
  state?: StartedGameResponse['state'];
}
