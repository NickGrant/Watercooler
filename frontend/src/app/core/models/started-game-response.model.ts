import { ActiveGameState } from './active-game-state.model';
import { GameSummary } from './game-summary.model';

export interface StartedGameResponse {
  game: GameSummary;
  state: ActiveGameState;
}
