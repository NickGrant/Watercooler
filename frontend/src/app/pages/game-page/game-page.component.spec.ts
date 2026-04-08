import { TestBed } from '@angular/core/testing';
import { ActivatedRoute, convertToParamMap } from '@angular/router';

import { GameSessionService } from '../../core/services/game-session.service';
import { GamePageComponent } from './game-page.component';

describe('GamePageComponent', () => {
  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [GamePageComponent],
      providers: [
        GameSessionService,
        {
          provide: ActivatedRoute,
          useValue: {
            snapshot: {
              paramMap: convertToParamMap({ slug: 'synergy-report-telemetry' })
            }
          }
        }
      ]
    }).compileComponents();
  });

  it('loads the route slug into the shared session service', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    expect(session.slug()).toBe('synergy-report-telemetry');
    expect(fixture.componentInstance.session).toBe(session);
  });

  it('updates the session stage when a shell state is selected', () => {
    const fixture = TestBed.createComponent(GamePageComponent);
    const session = TestBed.inject(GameSessionService);

    fixture.componentInstance.setStage('lobby');
    expect(session.stage()).toBe('lobby');
  });
});
