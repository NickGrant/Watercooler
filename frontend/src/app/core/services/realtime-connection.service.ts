import { DestroyRef, inject, Injectable, signal } from '@angular/core';
import { Subject } from 'rxjs';

export interface RealtimeEnvelope {
  event: string;
  payload: unknown;
}

@Injectable({
  providedIn: 'root'
})
export class RealtimeConnectionService {
  private readonly destroyRef = inject(DestroyRef);
  private readonly messageSubject = new Subject<RealtimeEnvelope>();
  private socket: WebSocket | null = null;
  private connectionKey: string | null = null;

  readonly messages$ = this.messageSubject.asObservable();
  readonly isConnected = signal(false);

  constructor() {
    this.destroyRef.onDestroy(() => {
      this.disconnect();
    });
  }

  connect(roomSlug: string, sessionToken: string): void {
    const nextKey = `${roomSlug}:${sessionToken}`;

    if (this.connectionKey === nextKey && this.socket !== null && this.socket.readyState <= WebSocket.OPEN) {
      return;
    }

    this.disconnect();

    const socket = new WebSocket(this.buildUrl());
    this.socket = socket;
    this.connectionKey = nextKey;

    socket.onopen = () => {
      this.isConnected.set(true);
      socket.send(
        JSON.stringify({
          type: 'join',
          slug: roomSlug,
          sessionToken
        })
      );
    };

    socket.onmessage = (event) => {
      const decoded = JSON.parse(String(event.data)) as RealtimeEnvelope;
      this.messageSubject.next(decoded);
    };

    socket.onerror = () => {
      this.isConnected.set(false);
    };

    socket.onclose = () => {
      if (this.socket === socket) {
        this.socket = null;
        this.connectionKey = null;
      }

      this.isConnected.set(false);
    };
  }

  disconnect(): void {
    const socket = this.socket;
    this.socket = null;
    this.connectionKey = null;
    this.isConnected.set(false);

    socket?.close();
  }

  private buildUrl(): string {
    const protocol = globalThis.location?.protocol === 'https:' ? 'wss' : 'ws';
    const hostname = globalThis.location?.hostname || 'localhost';

    return `${protocol}://${hostname}:8090`;
  }
}
