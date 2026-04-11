import {
  ActiveGameCard,
  ActiveGamePlayer,
  ActiveGameState,
  ActivePlayerCard,
  ResourceLedger,
  ResourceType
} from '../../core/models/active-game-state.model';

/**
 * Mirrors backend winner ordering so client-side standings and tie-break messaging
 * never drift from the authoritative endgame rules.
 */
export function sortPlayersByFinalStanding(players: ActiveGamePlayer[]): ActiveGamePlayer[] {
  return [...players].sort((left, right) => {
    const prestigeComparison = right.officePrestige - left.officePrestige;
    if (prestigeComparison !== 0) {
      return prestigeComparison;
    }

    const purchasedCardComparison = left.purchasedCardCount - right.purchasedCardCount;
    if (purchasedCardComparison !== 0) {
      return purchasedCardComparison;
    }

    return left.seatOrder - right.seatOrder;
  });
}

/**
 * Condenses the most relevant passive-update differences into a short toast message.
 * The UI intentionally caps this at two events to keep refresh notifications readable.
 */
export function describeStateChanges(
  previous: ActiveGameState | null,
  next: ActiveGameState,
  currentPlayerId: number | null | undefined
): string | null {
  if (previous === null) {
    return null;
  }

  const events: string[] = [];

  for (const player of next.players) {
    const previousPlayer = previous.players.find(
      (candidate) => candidate.gamePlayerId === player.gamePlayerId
    );

    if (previousPlayer === undefined) {
      continue;
    }

    const newPurchasedCard = player.purchasedCards.find(
      (card) => !previousPlayer.purchasedCards.some((candidate) => candidate.code === card.code)
    );
    if (newPurchasedCard !== undefined) {
      events.push(`${player.displayName} acquired ${newPurchasedCard.name}.`);
    }

    const newReservedCard = player.reservedCards.find(
      (card) => !previousPlayer.reservedCards.some((candidate) => candidate.code === card.code)
    );
    if (newReservedCard !== undefined) {
      events.push(`${player.displayName} claimed ${newReservedCard.name}.`);
    }

    const newExecutive = player.claimedExecutives.find(
      (executive) =>
        !previousPlayer.claimedExecutives.some((candidate) => candidate.code === executive.code)
    );
    if (newExecutive !== undefined) {
      events.push(`${player.displayName} secured ${newExecutive.name}.`);
    }
  }

  if (previous.currentTurnGamePlayerId !== next.currentTurnGamePlayerId) {
    const activePlayer = next.players.find(
      (player) => player.gamePlayerId === next.currentTurnGamePlayerId
    );

    if (activePlayer !== undefined) {
      events.push(
        activePlayer.gamePlayerId === currentPlayerId
          ? 'It is now your turn.'
          : `It is now ${activePlayer.displayName}'s turn.`
      );
    }
  }

  return events.length > 0 ? events.slice(0, 2).join(' ') : null;
}

export function formatRoomName(slug: string | null | undefined): string {
  if (typeof slug !== 'string' || slug.trim() === '') {
    return 'Unknown Room';
  }

  return slug
    .split('-')
    .filter((segment) => segment.trim().length > 0)
    .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
    .join(' ');
}

export function resourceLabel(resource: string): string {
  return resource === 'executiveFavor'
    ? 'Executive Favor'
    : resource.replace(/([A-Z])/g, ' $1').replace(/^./, (value) => value.toUpperCase());
}

export function resourceIconPath(resource: string): string {
  const iconName = resource === 'executiveFavor' ? 'executive-favor' : resource;

  return `/resources/${iconName}.png`;
}

export function resourceEntries(resources: Record<string, number>): Array<[string, number]> {
  return Object.entries(resources).filter(([, amount]) => amount > 0);
}

export function totalVisibleResources(resources: ResourceLedger): number {
  return resources.totalTokens
    ?? resources.coffee
    + resources.spreadsheets
    + resources.budget
    + resources.connections
    + resources.time
    + resources.executiveFavor;
}

export function isExecutiveRequirementMet(
  player: ActiveGamePlayer | null,
  resource: string,
  amount: number
): boolean {
  if (player === null) {
    return false;
  }

  return (player.permanentDiscounts[resource as ResourceType] ?? 0) >= amount;
}

/**
 * Resolves whether a player can pay for a card after applying permanent discounts,
 * then spends Executive Favor only on any remaining shortfall.
 */
export function canAffordCard(
  player: ActiveGamePlayer | null,
  card: ActivePlayerCard | ActiveGameCard
): boolean {
  if (player === null) {
    return false;
  }

  let remainingExecutiveFavor = player.resources.executiveFavor;

  return resourceEntries(card.cost).every(([resource, amount]) => {
    const permanentDiscount = player.permanentDiscounts[resource as ResourceType] ?? 0;
    const discountedCost = Math.max(0, amount - permanentDiscount);
    const available = player.resources[resource as keyof ResourceLedger];

    if (typeof available !== 'number') {
      return false;
    }

    if (available >= discountedCost) {
      return true;
    }

    const shortfall = discountedCost - available;

    if (shortfall > remainingExecutiveFavor) {
      return false;
    }

    remainingExecutiveFavor -= shortfall;
    return true;
  });
}

export function executiveFavorRequired(
  player: ActiveGamePlayer | null,
  card: ActivePlayerCard | ActiveGameCard
): number {
  if (player === null) {
    return 0;
  }

  let remainingExecutiveFavor = player.resources.executiveFavor;
  let requiredExecutiveFavor = 0;

  resourceEntries(card.cost).forEach(([resource, amount]) => {
    const permanentDiscount = player.permanentDiscounts[resource as ResourceType] ?? 0;
    const discountedCost = Math.max(0, amount - permanentDiscount);
    const available = player.resources[resource as keyof ResourceLedger];

    if (typeof available !== 'number' || available >= discountedCost) {
      return;
    }

    const shortfall = Math.min(discountedCost - available, remainingExecutiveFavor);
    requiredExecutiveFavor += shortfall;
    remainingExecutiveFavor -= shortfall;
  });

  return requiredExecutiveFavor;
}

export function canAddResourceToSelection(
  selectedResources: ResourceType[],
  resource: ResourceType,
  bank: ResourceLedger
): boolean {
  if ((bank[resource] ?? 0) < 1 || selectedResources.length >= 3) {
    return false;
  }

  const nextSelection = [...selectedResources, resource];
  const resourceCounts = new Map<ResourceType, number>();

  nextSelection.forEach((selectedResource) => {
    resourceCounts.set(selectedResource, (resourceCounts.get(selectedResource) ?? 0) + 1);
  });

  const counts = [...resourceCounts.values()];
  const distinctAvailableResources = ([
    'coffee',
    'spreadsheets',
    'budget',
    'connections',
    'time'
  ] as ResourceType[]).filter((resourceType) => (bank[resourceType] ?? 0) > 0).length;

  if (counts.some((count) => count > 2)) {
    return false;
  }

  if (nextSelection.length === 1) {
    return true;
  }

  if (nextSelection.length === 2) {
    if (resourceCounts.size === 1) {
      return (bank[resource] ?? 0) >= 4;
    }

    return resourceCounts.size === 2 && (distinctAvailableResources <= 2 || selectedResources.length === 1);
  }

  return resourceCounts.size === 3;
}

export function finalPlacementLabel(index: number): string {
  return ['1st', '2nd', '3rd', '4th'][index] ?? `${index + 1}th`;
}

/**
 * Converts the final ordered standings into the same tie-break explanation the results
 * screen shows players after the last round completes.
 */
export function buildFinalTieBreakSummary(standings: ActiveGamePlayer[]): string {
  const winner = standings[0] ?? null;

  if (winner === null) {
    return 'The final standings are unavailable.';
  }

  const tiedPlayers = standings.filter(
    (player) =>
      player.officePrestige === winner.officePrestige &&
      player.purchasedCardCount === winner.purchasedCardCount
  );

  if (tiedPlayers.length > 1) {
    return `${winner.displayName} won the tie on seat order after prestige and purchased-card count remained tied.`;
  }

  const runnerUp = standings[1] ?? null;
  if (runnerUp !== null && runnerUp.officePrestige === winner.officePrestige) {
    return `${winner.displayName} won the tie-break by finishing with fewer completed projects.`;
  }

  return `${winner.displayName} secured the win on Office Prestige.`;
}
