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
      body: 'body-2',
      face: 'face-3',
      hair: 'hair-4'
    });
    fixture.detectChanges();

    const images = fixture.nativeElement.querySelectorAll('img');

    expect(images.length).toBe(3);
    expect(images[0].getAttribute('src')).toContain('avatar-options/normalized/body/body-2.png');
    expect(images[1].getAttribute('src')).toContain('avatar-options/normalized/face/face-3.png');
    expect(images[2].getAttribute('src')).toContain('avatar-options/normalized/hair/hair-4.png');
  });

  it('maps legacy avatar ids onto the new normalized PNG layers', () => {
    fixture.componentRef.setInput('avatar', {
      body: 'blazer',
      face: 'corporate-neutral',
      hair: 'side-part'
    });
    fixture.detectChanges();

    const images = fixture.nativeElement.querySelectorAll('img');

    expect(images[0].getAttribute('src')).toContain('avatar-options/normalized/body/body-1.png');
    expect(images[1].getAttribute('src')).toContain('avatar-options/normalized/face/face-1.png');
    expect(images[2].getAttribute('src')).toContain('avatar-options/normalized/hair/hair-1.png');
  });
});
