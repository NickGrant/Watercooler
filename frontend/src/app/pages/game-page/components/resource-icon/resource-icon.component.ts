import { Component, computed, input } from '@angular/core';

@Component({
  selector: 'app-resource-icon',
  templateUrl: './resource-icon.component.html',
  styleUrl: './resource-icon.component.scss'
})
export class ResourceIconComponent {
  readonly src = input.required<string>();
  readonly label = input.required<string>();
  readonly value = input<number | string | null>(null);
  readonly variant = input<'regular' | 'small'>('regular');
  readonly decorative = input(false);

  readonly accessibilityLabel = computed(() => {
    if (this.value() === null || this.value() === undefined || this.value() === '') {
      return this.label();
    }

    return `${this.label()} ${this.value()}`;
  });
}
