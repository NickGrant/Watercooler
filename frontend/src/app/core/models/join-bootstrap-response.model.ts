import { GameSummary } from './game-summary.model';
import { JoinedPlayer } from './joined-player.model';

export interface JoinBootstrapResponse {
  game: GameSummary;
  player: JoinedPlayer;
  session: {
    token: string;
    reconnectEnabled: boolean;
  };
  lobby: {
    minimumPlayers: number;
    maximumPlayers: number;
    canStart: boolean;
    joinedPlayers: JoinedPlayer[];
  };
  realtime: {
    transport: string;
    roomSlug: string;
    sessionToken: string;
  };
}
