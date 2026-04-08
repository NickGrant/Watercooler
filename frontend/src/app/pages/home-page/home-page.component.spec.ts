import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { HomePageComponent } from './home-page.component';

describe('HomePageComponent', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [HomePageComponent],
      providers: [provideRouter([])]
    }).compileComponents();
  });

  it('exposes the preview slug used by the route shell button', () => {
    const fixture = TestBed.createComponent(HomePageComponent);
    expect(fixture.componentInstance.sampleSlug).toBe('synergy-report-telemetry');
  });

  it('renders the route preview button', () => {
    const fixture = TestBed.createComponent(HomePageComponent);
    fixture.detectChanges();

    const link = fixture.nativeElement.querySelector('a[mat-flat-button]');
    expect(link?.textContent).toContain('Preview Game Route Shell');
  });
});
