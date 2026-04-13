import { AvatarDraft } from '../models/avatar-draft.model';

export interface AvatarOptionDefinition {
  value: string;
  label: string;
  imagePath: string;
}

const AVATAR_OPTION_COUNT = 15;

function buildAvatarOptions(): AvatarOptionDefinition[] {
  return Array.from({ length: AVATAR_OPTION_COUNT }, (_, index) => {
    const number = index + 1;
    const value = `avatar-${number}`;

    return {
      value,
      label: `Avatar ${String(number).padStart(2, '0')}`,
      imagePath: `avatars/${value}.png`
    };
  });
}

export const AVATAR_OPTIONS: AvatarOptionDefinition[] = buildAvatarOptions();

export function resolveAvatarValue(value: string): string {
  return AVATAR_OPTIONS.find((option) => option.value === value)?.value ?? AVATAR_OPTIONS[0].value;
}

export function resolveAvatarOption(value: string): AvatarOptionDefinition {
  const resolvedValue = resolveAvatarValue(value);

  return AVATAR_OPTIONS.find((option) => option.value === resolvedValue) ?? AVATAR_OPTIONS[0];
}

export function resolveAvatarDraft(avatar: AvatarDraft): AvatarDraft {
  return {
    id: resolveAvatarValue(avatar.id)
  };
}
