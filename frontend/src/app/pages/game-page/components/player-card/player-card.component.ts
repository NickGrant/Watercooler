import { AfterViewInit, Component, DestroyRef, ElementRef, inject, input, output, viewChild } from '@angular/core';
import { MatTooltipModule } from '@angular/material/tooltip';

import { ActiveExecutive, ActiveGamePlayer, ActivePlayerCard, ResourceType } from '../../../../core/models/active-game-state.model';
import { ExecutiveCardComponent } from '../executive-card/executive-card.component';
import { ProjectCardComponent } from '../project-card/project-card.component';
import { ResourceIconComponent } from '../resource-icon/resource-icon.component';

@Component({
  selector: 'app-player-card',
  imports: [MatTooltipModule, ExecutiveCardComponent, ProjectCardComponent, ResourceIconComponent],
  templateUrl: './player-card.component.html',
  styleUrl: './player-card.component.scss'
})
export class PlayerCardComponent implements AfterViewInit {
  private readonly destroyRef = inject(DestroyRef);
  private readonly resourceBarSentinel = viewChild<ElementRef<HTMLElement>>('resourceBarSentinel');

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
  readonly stickyResourceVisibilityChange = output<boolean>();

  ngAfterViewInit(): void {
    const stripRef = this.resourceBarSentinel();

    if (stripRef === undefined || typeof IntersectionObserver === 'undefined') {
      return;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        this.stickyResourceVisibilityChange.emit(!entry.isIntersecting);
      },
      {
        threshold: 0,
        rootMargin: '-8px 0px 0px 0px'
      }
    );

    observer.observe(stripRef.nativeElement);
    this.destroyRef.onDestroy(() => observer.disconnect());
  }

  onPurchaseReserved(card: ActivePlayerCard | ActiveExecutive): void {
    this.purchaseReservedCard.emit(card as ActivePlayerCard);
  }
}
