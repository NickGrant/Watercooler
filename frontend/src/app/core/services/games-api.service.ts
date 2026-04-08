import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';

import { AvatarDraft } from '../models/avatar-draft.model';
import { GameResponse } from '../models/game-response.model';
import { GameSummary } from '../models/game-summary.model';
import { JoinBootstrapResponse } from '../models/join-bootstrap-response.model';
import { StartedGameResponse } from '../models/started-game-response.model';

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

  joinBootstrap(
    slug: string,
    payload: { displayName: string; avatar: AvatarDraft } | { sessionToken: string }
  ): Observable<JoinBootstrapResponse> {
    return this.http.post<JoinBootstrapResponse>(
      `/api/games/${encodeURIComponent(slug)}/join-bootstrap`,
      payload
    );
  }

  startGame(slug: string, payload: { sessionToken: string }): Observable<StartedGameResponse> {
    return this.http.post<StartedGameResponse>(`/api/games/${encodeURIComponent(slug)}/start`, payload);
  }
}
