import { Component, input, output } from '@angular/core';
import { MatButtonModule } from '@angular/material/button';
import { MatTooltipModule } from '@angular/material/tooltip';

import { ResourceLedger, ResourceType } from '../../../../core/models/active-game-state.model';
import { ResourceIconComponent } from '../resource-icon/resource-icon.component';

@Component({
  selector: 'app-resource-bank',
  imports: [MatButtonModule, MatTooltipModule, ResourceIconComponent],
  templateUrl: './resource-bank.component.html',
  styleUrl: './resource-bank.component.scss'
})
export class ResourceBankComponent {
  readonly resourceTypes = input.required<ResourceType[]>();
  readonly bank = input.required<ResourceLedger>();
  readonly selectedResources = input.required<ResourceType[]>();
  readonly actionPending = input(false);
  readonly isCurrentPlayersTurn = input(false);
  readonly resourceLabel = input.required<(resource: string) => string>();
  readonly resourceIconPath = input.required<(resource: string) => string>();

  readonly toggleResource = output<ResourceType>();
  readonly submitSelection = output<void>();

  onToggle(resource: ResourceType): void {
    this.toggleResource.emit(resource);
  }

  onSubmit(): void {
    this.submitSelection.emit();
  }
}
