import { GameSummary } from './game-summary.model';

export interface StartedGamePlayer {
  gamePlayerId: number;
  displayName: string;
  isHost: boolean;
  joinStatus: string;
  seatOrder: number;
  officePrestige: number;
}

export interface StartedGameCard {
  code: string;
  tier: number;
  name: string;
  resourceDiscount: string;
  officePrestige: number;
  cost: Record<string, number>;
  marketSlot: number;
}

export interface StartedExecutive {
  code: string;
  name: string;
  officePrestige: number;
  requirements: Record<string, number>;
  slotOrder: number;
}

export interface StartedGameResponse {
  game: GameSummary;
  state: {
    currentTurnGamePlayerId: number;
    players: StartedGamePlayer[];
    bank: Record<string, number>;
    market: {
      tier1: StartedGameCard[];
      tier2: StartedGameCard[];
      tier3: StartedGameCard[];
    };
    executives: StartedExecutive[];
  };
}
