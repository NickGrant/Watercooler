import { Component, input, output } from '@angular/core';

import { ActiveGameCard } from '../../../../core/models/active-game-state.model';
import { ProjectCardComponent } from '../project-card/project-card.component';

@Component({
  selector: 'app-visible-market',
  imports: [ProjectCardComponent],
  templateUrl: './visible-market.component.html',
  styleUrl: './visible-market.component.scss'
})
export class VisibleMarketComponent {
  readonly market = input.required<{
    tier1: ActiveGameCard[];
    tier2: ActiveGameCard[];
    tier3: ActiveGameCard[];
  }>();
  readonly isCompletedGame = input(false);
  readonly actionPending = input(false);
  readonly isCurrentPlayersTurn = input(false);
  readonly canPurchaseCard = input.required<(card: ActiveGameCard) => boolean>();
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();

  readonly claimCard = output<ActiveGameCard>();
  readonly purchaseCard = output<ActiveGameCard>();

  readonly tiers = [
    { label: 'Tier 1', key: 'tier1' as const },
    { label: 'Tier 2', key: 'tier2' as const },
    { label: 'Tier 3', key: 'tier3' as const }
  ];
}
