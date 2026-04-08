import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { GameResponse } from '../models/game-response.model';
import { GameSummary } from '../models/game-summary.model';

@Injectable({
  providedIn: 'root'
})
export class GamesApiService {
  private readonly http = inject(HttpClient);

  createGame(): Observable<GameSummary> {
    return this.http.post<GameResponse>('/api/games', {}).pipe(map((response) => response.game));
  }

  getGame(slug: string): Observable<GameSummary> {
    return this.http
      .get<GameResponse>(`/api/games/${encodeURIComponent(slug)}`)
      .pipe(map((response) => response.game));
  }
}
