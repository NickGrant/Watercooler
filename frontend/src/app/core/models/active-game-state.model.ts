export type ResourceType = 'coffee' | 'spreadsheets' | 'budget' | 'connections' | 'time';

export interface ResourceLedger {
  coffee: number;
  spreadsheets: number;
  budget: number;
  connections: number;
  time: number;
  executiveFavor: number;
  totalTokens?: number;
}

export interface ActiveGameCard {
  code: string;
  tier: number;
  name: string;
  resourceDiscount: string;
  officePrestige: number;
  cost: Record<string, number>;
  marketSlot: number;
}

export interface ActivePlayerCard {
  code: string;
  tier: number;
  name: string;
  resourceDiscount: string;
  officePrestige: number;
  cost: Record<string, number>;
}

export interface ActiveExecutive {
    code: string;
    name: string;
    portraitAsset?: string | null;
    officePrestige: number;
    requirements: Record<string, number>;
    slotOrder?: number;
}

export interface ActiveGamePlayer {
  gamePlayerId: number;
  displayName: string;
  isHost: boolean;
  joinStatus: string;
  seatOrder: number;
  officePrestige: number;
  resources: ResourceLedger;
  permanentDiscounts: Record<ResourceType, number>;
  reservedCards: ActivePlayerCard[];
  purchasedCards: ActivePlayerCard[];
  purchasedCardCount: number;
  claimedExecutives: ActiveExecutive[];
  claimedExecutiveCount: number;
}

export interface ActiveGameState {
  currentTurnGamePlayerId: number;
  players: ActiveGamePlayer[];
  bank: ResourceLedger;
  market: {
    tier1: ActiveGameCard[];
    tier2: ActiveGameCard[];
    tier3: ActiveGameCard[];
  };
  executives: ActiveExecutive[];
}
