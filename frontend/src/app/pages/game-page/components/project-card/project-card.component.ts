import { Component, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatTooltipModule } from '@angular/material/tooltip';

import { ActiveGameCard, ActivePlayerCard } from '../../../../core/models/active-game-state.model';
import { ResourceIconComponent } from '../resource-icon/resource-icon.component';

@Component({
  selector: 'app-project-card',
  imports: [MatButtonModule, MatTooltipModule, ResourceIconComponent],
  templateUrl: './project-card.component.html'
})
export class ProjectCardComponent {
  readonly card = input.required<ActiveGameCard | ActivePlayerCard>();
  readonly variant = input<'market' | 'reserved'>('market');
  readonly isCompletedGame = input(false);
  readonly actionPending = input(false);
  readonly canAct = input(false);
  readonly canPurchase = input(false);
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();

  readonly claimCard = output<ActiveGameCard>();
  readonly purchaseCard = output<ActiveGameCard | ActivePlayerCard>();

  onClaim(): void {
    if (this.variant() === 'market') {
      this.claimCard.emit(this.card() as ActiveGameCard);
    }
  }

  onPurchase(): void {
    this.purchaseCard.emit(this.card());
  }

  resourceEntries(): Array<[string, number]> {
    return Object.entries(this.card().cost).filter(([, amount]) => amount > 0);
  }
}
