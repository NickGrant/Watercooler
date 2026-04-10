import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ActiveGamePlayer } from '../../../../core/models/active-game-state.model';
import { PlayerCardComponent } from './player-card.component';

describe('PlayerCardComponent', () => {
  let fixture: ComponentFixture<PlayerCardComponent>;
  let player: ActiveGamePlayer;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PlayerCardComponent]
    }).compileComponents();

    player = {
      gamePlayerId: 1,
      displayName: 'Pam',
      isHost: true,
      joinStatus: 'connected',
      seatOrder: 1,
      officePrestige: 0,
      resources: {
        coffee: 1,
        spreadsheets: 1,
        budget: 1,
        connections: 0,
        time: 0,
        executiveFavor: 0,
        totalTokens: 3
      },
      permanentDiscounts: {
        coffee: 0,
        spreadsheets: 0,
        budget: 0,
        connections: 0,
        time: 0
      },
      reservedCards: [
        {
          code: 'reserved-1',
          tier: 1,
          name: 'Conference Room Coup',
          resourceDiscount: 'coffee',
          officePrestige: 0,
          cost: {
            coffee: 0,
            spreadsheets: 1,
            budget: 1,
            connections: 1,
            time: 0
          }
        }
      ],
      purchasedCards: [],
      purchasedCardCount: 0,
      claimedExecutives: [],
      claimedExecutiveCount: 0
    };

    fixture = TestBed.createComponent(PlayerCardComponent);
    fixture.componentRef.setInput('player', player);
    fixture.componentRef.setInput('resourceTypes', ['coffee', 'spreadsheets', 'budget', 'connections', 'time']);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.svg`);
    fixture.componentRef.setInput('canPurchaseReservedCard', () => true);
    fixture.componentRef.setInput('isCurrentPlayersTurn', true);
    fixture.componentRef.setInput('isCurrentUserCard', true);
  });

  it('renders a player card with economy bars', () => {
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('.player-panel')).not.toBeNull();
    expect(fixture.nativeElement.querySelectorAll('.player-resource-bar').length).toBe(5);
    expect(fixture.nativeElement.textContent).toContain('Pam');
  });

  it('emits purchases from reserved cards', () => {
    const purchaseSpy = jasmine.createSpy('purchase');
    fixture.componentRef.instance.purchaseReservedCard.subscribe(purchaseSpy);
    fixture.detectChanges();

    const button = fixture.nativeElement.querySelector('.mini-card button') as HTMLButtonElement;
    button.click();

    expect(purchaseSpy).toHaveBeenCalledWith(player.reservedCards[0]);
  });
});
