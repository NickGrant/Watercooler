import { ComponentFixture, TestBed } from '@angular/core/testing';

import { AvatarCompositeComponent } from './avatar-composite.component';

describe('AvatarCompositeComponent', () => {
  let fixture: ComponentFixture<AvatarCompositeComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [AvatarCompositeComponent]
    }).compileComponents();

    fixture = TestBed.createComponent(AvatarCompositeComponent);
  });

  it('renders normalized PNG layers for the provided avatar', () => {
    fixture.componentRef.setInput('avatar', {
      id: 'avatar-2'
    });
    fixture.detectChanges();

    const images = fixture.nativeElement.querySelectorAll('img');

    expect(images.length).toBe(1);
    expect(images[0].getAttribute('src')).toContain('avatars/avatar-2.png');
  });

  it('falls back to the first supported avatar when the value is unknown', () => {
    fixture.componentRef.setInput('avatar', {
      id: 'unknown-avatar'
    });
    fixture.detectChanges();

    const images = fixture.nativeElement.querySelectorAll('img');

    expect(images[0].getAttribute('src')).toContain('avatars/avatar-1.png');
  });
});
