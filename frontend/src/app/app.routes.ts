import { Routes } from '@angular/router';

import { GamePageComponent } from './pages/game-page/game-page.component';
import { HomePageComponent } from './pages/home-page/home-page.component';

export const routes: Routes = [
  {
    path: '',
    component: HomePageComponent
  },
  {
    path: 'game/:slug',
    component: GamePageComponent
  },
  {
    path: '**',
    redirectTo: ''
  }
];
