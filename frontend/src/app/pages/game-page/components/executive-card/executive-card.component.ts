import { Component, input } from '@angular/core';
import { MatTooltipModule } from '@angular/material/tooltip';

import { ActiveExecutive } from '../../../../core/models/active-game-state.model';

@Component({
  selector: 'app-executive-card',
  imports: [MatTooltipModule],
  templateUrl: './executive-card.component.html'
})
export class ExecutiveCardComponent {
  readonly executive = input.required<ActiveExecutive>();
  readonly variant = input<'board' | 'claimed'>('board');
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();

  resourceEntries(): Array<[string, number]> {
    return Object.entries(this.executive().requirements).filter(([, amount]) => amount > 0);
  }
}
