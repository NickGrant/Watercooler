import { Component, input } from '@angular/core';

import { ActiveExecutive } from '../../../../core/models/active-game-state.model';
import { ExecutiveCardComponent } from '../executive-card/executive-card.component';

@Component({
  selector: 'app-executive-row',
  imports: [ExecutiveCardComponent],
  templateUrl: './executive-row.component.html'
})
export class ExecutiveRowComponent {
  readonly executives = input.required<ActiveExecutive[]>();
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();
}
