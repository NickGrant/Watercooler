import { Component, input, output } from '@angular/core';
import { MatTooltipModule } from '@angular/material/tooltip';

import { ActiveExecutive, ActiveGamePlayer, ActivePlayerCard, ResourceLedger, ResourceType } from '../../../../core/models/active-game-state.model';
import { ExecutiveCardComponent } from '../executive-card/executive-card.component';
import { ProjectCardComponent } from '../project-card/project-card.component';

@Component({
  selector: 'app-player-card',
  imports: [MatTooltipModule, ExecutiveCardComponent, ProjectCardComponent],
  templateUrl: './player-card.component.html'
})
export class PlayerCardComponent {
  readonly player = input.required<ActiveGamePlayer>();
  readonly resourceTypes = input.required<ResourceType[]>();
  readonly isCompletedGame = input(false);
  readonly isActiveTurn = input(false);
  readonly isWinner = input(false);
  readonly placementLabel = input<string | null>(null);
  readonly actionPending = input(false);
  readonly isCurrentPlayersTurn = input(false);
  readonly isCurrentUserCard = input(false);
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();
  readonly canPurchaseReservedCard = input.required<(card: ActivePlayerCard) => boolean>();

  readonly purchaseReservedCard = output<ActivePlayerCard>();

  totalVisibleResources(resources: ResourceLedger): number {
    return resources.totalTokens ?? resources.coffee + resources.spreadsheets + resources.budget + resources.connections + resources.time + resources.executiveFavor;
  }

  onPurchaseReserved(card: ActivePlayerCard | ActiveExecutive): void {
    this.purchaseReservedCard.emit(card as ActivePlayerCard);
  }
}
