import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ExecutiveRowComponent } from './executive-row.component';

describe('ExecutiveRowComponent', () => {
  let fixture: ComponentFixture<ExecutiveRowComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ExecutiveRowComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ExecutiveRowComponent);
    fixture.componentRef.setInput('executives', [
      {
        code: 'vp-of-synergy',
        name: 'VP of Synergy',
        officePrestige: 3,
        requirements: { coffee: 3, spreadsheets: 0, budget: 3, connections: 3, time: 0 },
        slotOrder: 1
      }
    ]);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.svg`);
  });

  it('renders executive cards', () => {
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelector('app-executive-card')).not.toBeNull();
    expect(fixture.nativeElement.textContent).toContain('VP of Synergy');
  });
});
