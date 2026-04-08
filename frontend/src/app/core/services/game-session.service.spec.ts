import { TestBed } from '@angular/core/testing';

import { DEFAULT_AVATAR_DRAFT } from '../models/avatar-draft.model';
import { GameSessionService } from './game-session.service';

describe('GameSessionService', () => {
  let service: GameSessionService;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(GameSessionService);
  });

  it('starts with the expected default state', () => {
    expect(service.slug()).toBeNull();
    expect(service.stage()).toBe('pre-join');
    expect(service.stageLabel()).toBe('Awaiting employee identification');
    expect(service.playerName()).toBe('');
    expect(service.avatarDraft()).toEqual(DEFAULT_AVATAR_DRAFT);
  });

  it('updates stage labels when the stage changes', () => {
    service.setStage('lobby');
    expect(service.stageLabel()).toBe('Awaiting executive approval to start');

    service.setStage('in-game');
    expect(service.stageLabel()).toBe('Live workplace maneuvering in progress');
  });

  it('patches avatar data without discarding existing values', () => {
    service.patchAvatarDraft({ hair: 'executive-swoop' });

    expect(service.avatarDraft()).toEqual({
      ...DEFAULT_AVATAR_DRAFT,
      hair: 'executive-swoop'
    });
  });
});
