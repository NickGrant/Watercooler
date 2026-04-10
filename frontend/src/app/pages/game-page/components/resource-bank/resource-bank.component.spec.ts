import { ComponentFixture, TestBed } from '@angular/core/testing';

import { ResourceBankComponent } from './resource-bank.component';

describe('ResourceBankComponent', () => {
  let fixture: ComponentFixture<ResourceBankComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [ResourceBankComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(ResourceBankComponent);
    fixture.componentRef.setInput('resourceTypes', ['coffee', 'spreadsheets', 'budget', 'connections', 'time']);
    fixture.componentRef.setInput('bank', {
      coffee: 4,
      spreadsheets: 4,
      budget: 4,
      connections: 4,
      time: 4,
      executiveFavor: 5
    });
    fixture.componentRef.setInput('selectedResources', ['coffee', 'budget']);
    fixture.componentRef.setInput('isCurrentPlayersTurn', true);
    fixture.componentRef.setInput('resourceLabel', (resource: string) => resource);
    fixture.componentRef.setInput('resourceIconPath', (resource: string) => `/icons/${resource}.png`);
  });

  it('renders the compact bank row with selected icons', () => {
    fixture.detectChanges();

    expect(fixture.nativeElement.querySelectorAll('.resource-bank__tile').length).toBe(6);
    expect(fixture.nativeElement.querySelectorAll('.selection-copy__icon').length).toBe(2);
  });

  it('emits resource selection changes', () => {
    const toggleSpy = jasmine.createSpy('toggle');
    fixture.componentRef.instance.toggleResource.subscribe(toggleSpy);
    fixture.detectChanges();

    (fixture.nativeElement.querySelector('.resource-bank__tile--selectable') as HTMLButtonElement).click();

    expect(toggleSpy).toHaveBeenCalledWith('coffee');
  });
});
