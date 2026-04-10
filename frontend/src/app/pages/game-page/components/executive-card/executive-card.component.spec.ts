import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ActiveExecutive } from '../../../../core/models/active-game-state.model';
import { ExecutiveCardComponent } from './executive-card.component';

describe('ExecutiveCardComponent', () => {
  let fixture: ComponentFixture<ExecutiveCardComponent>;

  const executive: ActiveExecutive = {
    code: 'vp-of-synergy',
    name: 'VP of Synergy',
    portraitAsset: 'executive-1.png',
    officePrestige: 3,
    requirements: { coffee: 3, spreadsheets: 0, budget: 3, connections: 3, time: 0 },
    slotOrder: 1
  };

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ExecutiveCardComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ExecutiveCardComponent);
    fixture.componentRef.setInput('executive', executive);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.svg`);
  });

  it('renders the board executive variant', () => {
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('.executive-card')).not.toBeNull();
    expect(fixture.nativeElement.textContent).toContain('VP of Synergy');
    expect(fixture.nativeElement.textContent).toContain('Requirement Profile');
    expect(fixture.nativeElement.querySelector('.executive-card__portrait-image')?.getAttribute('src')).toContain('/executives/executive-1.png');
  });

  it('renders the claimed executive variant', () => {
    fixture.componentRef.setInput('variant', 'claimed');
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('.mini-card--executive')).not.toBeNull();
  });
});
