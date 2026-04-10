import { TestBed } from '@angular/core/testing';

import { RealtimeConnectionService } from './realtime-connection.service';

describe('RealtimeConnectionService', () => {
  let service: RealtimeConnectionService;
  let sockets: FakeWebSocket[];
  let originalWebSocket: typeof WebSocket;

  beforeEach(() => {
    sockets = [];
    originalWebSocket = globalThis.WebSocket;
    globalThis.WebSocket = FakeWebSocket as unknown as typeof WebSocket;
    FakeWebSocket.onCreate = (socket) => {
      sockets.push(socket);
    };

    TestBed.configureTestingModule({});
    service = TestBed.inject(RealtimeConnectionService);
  });

  afterEach(() => {
    service.disconnect();
    globalThis.WebSocket = originalWebSocket;
  });

  it('opens a websocket and sends a join payload after connecting', () => {
    service.connect('synergy-report-telemetry', 'temporary-session-token');

    expect(sockets.length).toBe(1);
    expect(sockets[0].url).toContain(':8090');

    sockets[0].open();

    expect(service.isConnected()).toBeTrue();
    expect(JSON.parse(sockets[0].sentMessages[0])).toEqual({
      type: 'join',
      slug: 'synergy-report-telemetry',
      sessionToken: 'temporary-session-token'
    });
  });

  it('publishes parsed realtime messages', () => {
    const received: Array<{ event: string; payload: unknown }> = [];
    service.messages$.subscribe((message) => {
      received.push(message);
    });

    service.connect('synergy-report-telemetry', 'temporary-session-token');
    sockets[0].open();
    sockets[0].message(
      JSON.stringify({
        event: 'game.state.sync',
        payload: {
          game: {
            slug: 'synergy-report-telemetry'
          }
        }
      })
    );

    expect(received).toEqual([
      {
        event: 'game.state.sync',
        payload: {
          game: {
            slug: 'synergy-report-telemetry'
          }
        }
      }
    ]);
  });

  it('closes the prior socket before reconnecting to a new room token pair', () => {
    service.connect('room-a', 'token-a');
    const firstSocket = sockets[0];

    service.connect('room-b', 'token-b');

    expect(firstSocket.wasClosed).toBeTrue();
    expect(sockets.length).toBe(2);
  });
});

class FakeWebSocket {
  static readonly CONNECTING = 0;
  static readonly OPEN = 1;
  static readonly CLOSING = 2;
  static readonly CLOSED = 3;
  static onCreate: ((socket: FakeWebSocket) => void) | null = null;

  readonly sentMessages: string[] = [];
  readyState = FakeWebSocket.CONNECTING;
  wasClosed = false;
  onopen: (() => void) | null = null;
  onmessage: ((event: { data: string }) => void) | null = null;
  onerror: (() => void) | null = null;
  onclose: (() => void) | null = null;

  constructor(public readonly url: string) {
    FakeWebSocket.onCreate?.(this);
  }

  send(message: string): void {
    this.sentMessages.push(message);
  }

  close(): void {
    this.readyState = FakeWebSocket.CLOSED;
    this.wasClosed = true;
    this.onclose?.();
  }

  open(): void {
    this.readyState = FakeWebSocket.OPEN;
    this.onopen?.();
  }

  message(data: string): void {
    this.onmessage?.({ data });
  }
}
