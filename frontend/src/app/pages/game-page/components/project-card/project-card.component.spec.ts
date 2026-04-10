import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ActiveGameCard, ActivePlayerCard } from '../../../../core/models/active-game-state.model';
import { ProjectCardComponent } from './project-card.component';

describe('ProjectCardComponent', () => {
  let fixture: ComponentFixture<ProjectCardComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ProjectCardComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ProjectCardComponent);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.svg`);
  });

  it('renders a market card and emits claim actions', () => {
    const card: ActiveGameCard = {
      code: 'market-1',
      tier: 1,
      name: 'Budget Buffer',
      resourceDiscount: 'coffee',
      officePrestige: 1,
      cost: { coffee: 2, spreadsheets: 0, budget: 1, connections: 0, time: 0 },
      marketSlot: 1
    };
    const claimSpy = jasmine.createSpy('claim');

    fixture.componentRef.setInput('card', card);
    fixture.componentRef.setInput('variant', 'market');
    fixture.componentRef.setInput('canAct', true);
    fixture.componentRef.instance.claimCard.subscribe(claimSpy);
    fixture.detectChanges();

    (fixture.nativeElement.querySelector('button') as HTMLButtonElement).click();

    expect(fixture.nativeElement.textContent).toContain('Budget Buffer');
    expect(claimSpy).toHaveBeenCalledWith(card);
  });

  it('renders a reserved card and emits purchase actions', () => {
    const card: ActivePlayerCard = {
      code: 'reserved-1',
      tier: 1,
      name: 'Conference Room Coup',
      resourceDiscount: 'budget',
      officePrestige: 0,
      cost: { coffee: 0, spreadsheets: 1, budget: 1, connections: 1, time: 0 }
    };
    const purchaseSpy = jasmine.createSpy('purchase');

    fixture.componentRef.setInput('card', card);
    fixture.componentRef.setInput('variant', 'reserved');
    fixture.componentRef.setInput('canAct', true);
    fixture.componentRef.setInput('canPurchase', true);
    fixture.componentRef.instance.purchaseCard.subscribe(purchaseSpy);
    fixture.detectChanges();

    const button = fixture.nativeElement.querySelector('button') as HTMLButtonElement;
    button.click();

    expect(fixture.nativeElement.textContent).toContain('Purchase From Tray');
    expect(purchaseSpy).toHaveBeenCalledWith(card);
  });
});
