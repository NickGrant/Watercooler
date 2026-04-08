export interface AvatarDraft {
  body: string;
  face: string;
  hair: string;
}

export const DEFAULT_AVATAR_DRAFT: AvatarDraft = {
  body: 'blazer',
  face: 'corporate-neutral',
  hair: 'side-part'
};
