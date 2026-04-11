import { Component, computed, input } from '@angular/core';

import { resolveAvatarDraft } from '../../../core/avatar/avatar-options';
import { AvatarDraft } from '../../../core/models/avatar-draft.model';

@Component({
  selector: 'app-avatar-composite',
  templateUrl: './avatar-composite.component.html',
  styleUrl: './avatar-composite.component.scss'
})
export class AvatarCompositeComponent {
  readonly avatar = input.required<AvatarDraft>();
  readonly ariaLabel = input('Avatar preview');
  readonly size = input<'small' | 'medium' | 'large' | 'xlarge'>('medium');

  readonly normalizedAvatar = computed(() => resolveAvatarDraft(this.avatar()));
}
