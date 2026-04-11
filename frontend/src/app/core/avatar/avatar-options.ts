import { AvatarDraft } from '../models/avatar-draft.model';

export type AvatarPart = keyof AvatarDraft;

export interface AvatarOptionDefinition {
  value: string;
  label: string;
  previewPath: string;
  layerPath: string;
}

const AVATAR_OPTION_COUNTS: Record<AvatarPart, number> = {
  body: 10,
  face: 9,
  hair: 12
};

const AVATAR_LABEL_PREFIX: Record<AvatarPart, string> = {
  body: 'Outfit',
  face: 'Face',
  hair: 'Hair'
};

const LEGACY_AVATAR_ALIASES: Record<AvatarPart, Record<string, string>> = {
  body: {
    blazer: 'body-1',
    hoodie: 'body-2',
    cardigan: 'body-3',
    polo: 'body-4',
    'power-suit': 'body-5'
  },
  face: {
    'corporate-neutral': 'face-1',
    'coffee-grin': 'face-2',
    'meeting-fatigue': 'face-3',
    'deadline-focus': 'face-4',
    'visionary-smirk': 'face-5'
  },
  hair: {
    'side-part': 'hair-1',
    'executive-swoop': 'hair-2',
    'startup-mess': 'hair-3',
    'weekend-buzz': 'hair-4',
    'presentation-curl': 'hair-5'
  }
};

function buildAvatarOptions(part: AvatarPart): AvatarOptionDefinition[] {
  return Array.from({ length: AVATAR_OPTION_COUNTS[part] }, (_, index) => {
    const number = index + 1;
    const value = `${part}-${number}`;

    return {
      value,
      label: `${AVATAR_LABEL_PREFIX[part]} ${String(number).padStart(2, '0')}`,
      previewPath: `avatar-options/${part}/${value}.png`,
      layerPath: `avatar-options/normalized/${part}/${value}.png`
    };
  });
}

export const AVATAR_OPTIONS: Record<AvatarPart, AvatarOptionDefinition[]> = {
  body: buildAvatarOptions('body'),
  face: buildAvatarOptions('face'),
  hair: buildAvatarOptions('hair')
};

export function resolveAvatarValue(part: AvatarPart, value: string): string {
  const candidateValue = LEGACY_AVATAR_ALIASES[part][value] ?? value;

  return (
    AVATAR_OPTIONS[part].find((option) => option.value === candidateValue)?.value ??
    AVATAR_OPTIONS[part][0].value
  );
}

export function resolveAvatarOption(part: AvatarPart, value: string): AvatarOptionDefinition {
  const resolvedValue = resolveAvatarValue(part, value);

  return AVATAR_OPTIONS[part].find((option) => option.value === resolvedValue) ?? AVATAR_OPTIONS[part][0];
}

export function resolveAvatarDraft(avatar: AvatarDraft): AvatarDraft {
  return {
    body: resolveAvatarValue('body', avatar.body),
    face: resolveAvatarValue('face', avatar.face),
    hair: resolveAvatarValue('hair', avatar.hair)
  };
}
