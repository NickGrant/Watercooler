import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ActiveGameCard } from '../../../../core/models/active-game-state.model';
import { VisibleMarketComponent } from './visible-market.component';

describe('VisibleMarketComponent', () => {
  let fixture: ComponentFixture<VisibleMarketComponent>;

  const card: ActiveGameCard = {
    code: 'market-1',
    tier: 1,
    name: 'Budget Buffer',
    resourceDiscount: 'coffee',
    officePrestige: 1,
    cost: { coffee: 2, spreadsheets: 0, budget: 1, connections: 0, time: 0 },
    marketSlot: 1
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [VisibleMarketComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(VisibleMarketComponent);
    fixture.componentRef.setInput('market', {
      tier1: [card],
      tier2: [],
      tier3: []
    });
    fixture.componentRef.setInput('isCurrentPlayersTurn', true);
    fixture.componentRef.setInput('canPurchaseCard', () => true);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.png`);
  });

  it('renders market tiers and cards', () => {
    fixture.detectChanges();

    expect(fixture.nativeElement.textContent).toContain('Tier 1');
    expect(fixture.nativeElement.textContent).toContain('Budget Buffer');
    expect(fixture.nativeElement.querySelector('.market-tier__cards--structured')).not.toBeNull();
  });
});
