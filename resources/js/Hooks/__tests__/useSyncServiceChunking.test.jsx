import { describe, it, expect, beforeEach, vi } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { useSyncService } from '../useSyncService';

// ── Mock modules (hoisted by vitest) ──────────────────
// All mock variables MUST use vi.hoisted() because vi.mock factories
// are compiled to the very top of the file.

const { mockAxiosPost, mockIsAxiosError } = vi.hoisted(() => ({
  mockAxiosPost: vi.fn(),
  mockIsAxiosError: vi.fn((err) => err?.isAxiosError === true),
}));

const { mockToArray, mockAnyOf, mockWhere } = vi.hoisted(() => {
  const t = vi.fn();
  return {
    mockToArray: t,
    mockAnyOf: vi.fn(() => ({ toArray: t })),
    mockWhere: vi.fn(() => ({ anyOf: vi.fn(() => ({ toArray: t })) })),
  };
});

const { mockGetQueueCount, mockMarkSynced, mockMarkFailed } = vi.hoisted(() => ({
  mockGetQueueCount: vi.fn(),
  mockMarkSynced: vi.fn(),
  mockMarkFailed: vi.fn(),
}));

const { networkStatus } = vi.hoisted(() => ({ networkStatus: { isOnline: false } }));

// Mutable SYNC_CONFIG so each test can set its own chunk_size.
// calcChunkSize() reads properties at call time, so mutating this object works.
const { syncConfig } = vi.hoisted(() => ({
  syncConfig: {
    vercelTimeoutMs: 55_000,
    phpColdStartMs: 250,
    safetyMarginMs: 5_000,
    timePerOrderMs: 111,
    minChunkSize: 10,
    maxChunkSize: 100,
  },
}));

vi.mock('@/Hooks/useNetworkStatus', () => ({
  useNetworkStatus: () => ({ isOnline: networkStatus.isOnline }),
}));

vi.mock('axios', () => ({
  default: {
    post: (...args) => mockAxiosPost(...args),
    isAxiosError: (...args) => mockIsAxiosError(...args),
  },
}));

vi.mock('@/db/dexie-config', () => ({
  db: {
    offlineOrders: {
      where: mockWhere,
    },
  },
}));

vi.mock('@/db/offlineOrderStore', () => ({
  getQueueCount: (...args) => mockGetQueueCount(...args),
  markSynced: (...args) => mockMarkSynced(...args),
  markFailed: (...args) => mockMarkFailed(...args),
}));

vi.mock('@/config/sync', () => ({
  SYNC_CONFIG: syncConfig,
}));

// ── Helpers ───────────────────────────────────────────

/**
 * Create `count` pending order objects as returned by Dexie's .toArray().
 * Mirrors the helper in useSyncService.test.jsx.
 */
function createOrders(count) {
  return Array.from({ length: count }, (_, i) => ({
    localId: i + 1,
    uuid: `uuid-${i + 1}`,
    payload: JSON.stringify({
      uuid: `uuid-${i + 1}`,
      items: [
        { menuId: 1, name: 'Kopi', qty: 1, price: 12000, subtotal: 12000 },
      ],
      paymentMethod: 'cash',
      customerName: 'Test',
      isMahasiswa: false,
      total: 12000,
      createdAt: new Date(Date.now() - i * 1000).toISOString(),
    }),
    status: 'pending_sync',
    error: null,
    createdAt: new Date(Date.now() - i * 1000).toISOString(),
  }));
}

/**
 * Flush all pending microtasks then flush React state updates.
 */
function flushMicrotasks() {
  return act(() => new Promise((resolve) => setTimeout(resolve, 0)));
}

/**
 * Override SYNC_CONFIG so calcChunkSize() returns exactly `size`.
 * Formula: floor((vercelTimeoutMs - phpColdStartMs - safetyMarginMs) / timePerOrderMs)
 * Clamped between minChunkSize and maxChunkSize.
 *
 * Set all dimensions to `size` so the division yields exactly `size`.
 */
function setChunkSize(size) {
  Object.assign(syncConfig, {
    vercelTimeoutMs: size * 100, // budget = size * 100
    phpColdStartMs: 0,
    safetyMarginMs: 0,
    timePerOrderMs: 100, // floor(budget / 100) = size
    minChunkSize: size,
    maxChunkSize: size,
  });
}

/** Restore default SYNC_CONFIG values (chunk_size = 100, capped from 448). */
function resetChunkSize() {
  Object.assign(syncConfig, {
    vercelTimeoutMs: 55_000,
    phpColdStartMs: 250,
    safetyMarginMs: 5_000,
    timePerOrderMs: 111,
    minChunkSize: 10,
    maxChunkSize: 100,
  });
}

/**
 * Mock axios.post to return a successful response for every chunk,
 * dynamically generating synced entries from the actual chunk payload.
 */
function mockSuccessfulSync() {
  mockAxiosPost.mockImplementation((_url, { orders: chunk }) =>
    Promise.resolve({
      data: {
        synced: chunk.map((o) => ({
          localUuid: o.uuid,
          serverOrderCode: `ORD-${o.uuid.split('-')[1].padStart(3, '0')}`,
          serverId: parseInt(o.uuid.split('-')[1], 10),
        })),
        failed: [],
        summary: { total: chunk.length, synced: chunk.length, failed: 0 },
      },
    }),
  );
}

// ── Tests ─────────────────────────────────────────────

describe('useSyncService — Chunking', () => {
  beforeEach(() => {
    networkStatus.isOnline = false;
    vi.clearAllMocks();
    mockGetQueueCount.mockResolvedValue(0);
    resetChunkSize();
  });

  // ──────────────────────────────────────────────────────
  // 1. 150 orders split into 3 chunks of 50
  // ──────────────────────────────────────────────────────

  it('test_150_orders_split_into_3_chunks_of_50', async () => {
    setChunkSize(50);

    const orders = createOrders(150);
    mockToArray.mockResolvedValue(orders);
    mockSuccessfulSync();

    const { rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // Assert 3 POST calls
    expect(mockAxiosPost).toHaveBeenCalledTimes(3);

    const calls = mockAxiosPost.mock.calls;

    // Chunk 1: orders 1-50
    expect(calls[0][1].orders).toHaveLength(50);
    expect(calls[0][1].orders[0].uuid).toBe('uuid-1');
    expect(calls[0][1].orders[49].uuid).toBe('uuid-50');

    // Chunk 2: orders 51-100
    expect(calls[1][1].orders).toHaveLength(50);
    expect(calls[1][1].orders[0].uuid).toBe('uuid-51');
    expect(calls[1][1].orders[49].uuid).toBe('uuid-100');

    // Chunk 3: orders 101-150
    expect(calls[2][1].orders).toHaveLength(50);
    expect(calls[2][1].orders[0].uuid).toBe('uuid-101');
    expect(calls[2][1].orders[49].uuid).toBe('uuid-150');

    // All 150 marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(150);
  });

  // ──────────────────────────────────────────────────────
  // 2. Chunk size calculated from model
  //    calcChunkSize = floor((55000-250-5000)/111) = 448
  //    Capped at maxChunkSize = 100
  //    → 120 orders = 2 chunks (100 + 20)
  // ──────────────────────────────────────────────────────

  it('test_chunk_size_calculated_from_model', async () => {
    // Default SYNC_CONFIG → chunk_size = 100 (448 capped at maxChunkSize)

    const orders = createOrders(120);
    mockToArray.mockResolvedValue(orders);
    mockSuccessfulSync();

    const { rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // 2 chunks: ceil(120/100) = 2
    expect(mockAxiosPost).toHaveBeenCalledTimes(2);

    const calls = mockAxiosPost.mock.calls;
    // First chunk: 100 orders (max)
    expect(calls[0][1].orders).toHaveLength(100);
    // Second chunk: remaining 20
    expect(calls[1][1].orders).toHaveLength(20);

    // Model calc verification:
    //   budget = 55000 - 250 - 5000 = 49750 ms
    //   raw = floor(49750 / 111) = 448
    //   clamped to maxChunkSize (100)
    // Proof: 120 orders split into 2 chunks, not 1 chunk of 120
    expect(mockMarkSynced).toHaveBeenCalledTimes(120);
  });

  // ──────────────────────────────────────────────────────
  // 3. Small batch fits in one chunk
  // ──────────────────────────────────────────────────────

  it('test_small_batch_fits_in_one_chunk', async () => {
    setChunkSize(50);

    const orders = createOrders(5);
    mockToArray.mockResolvedValue(orders);
    mockSuccessfulSync();

    const { rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // Exactly 1 POST call with all 5 orders
    expect(mockAxiosPost).toHaveBeenCalledTimes(1);
    expect(mockAxiosPost.mock.calls[0][1].orders).toHaveLength(5);
    expect(mockAxiosPost).toHaveBeenCalledWith(
      '/api/sync-orders',
      expect.objectContaining({ orders: expect.any(Array) }),
    );

    // All 5 marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(5);
  });

  // ──────────────────────────────────────────────────────
  // 4. Chunk progress tracks correctly
  //    120 orders, chunk_size=50 → 3 chunks
  //    progress.current: 1→2→3, progress.total: 3
  // ──────────────────────────────────────────────────────

  it('test_chunk_progress_tracks_correctly', async () => {
    setChunkSize(50);

    const orders = createOrders(120);
    mockToArray.mockResolvedValue(orders);

    // Deferred axios resolutions so we can inspect state between chunks
    let resolveChunk1, resolveChunk2, resolveChunk3;
    const chunk1Deferred = new Promise((r) => { resolveChunk1 = r; });
    const chunk2Deferred = new Promise((r) => { resolveChunk2 = r; });
    const chunk3Deferred = new Promise((r) => { resolveChunk3 = r; });
    const resolvers = [chunk1Deferred, chunk2Deferred, chunk3Deferred];

    let callCount = 0;
    mockAxiosPost.mockImplementation((_url, { orders: chunk }) => {
      const deferred = resolvers[callCount++];
      return deferred.then(() => ({
        data: {
          synced: chunk.map((o) => ({
            localUuid: o.uuid,
            serverOrderCode: `ORD-${o.uuid.split('-')[1].padStart(3, '0')}`,
            serverId: parseInt(o.uuid.split('-')[1], 10),
          })),
          failed: [],
          summary: { total: chunk.length, synced: chunk.length, failed: 0 },
        },
      }));
    });

    const { result, rerender } = renderHook(() => useSyncService());

    // Trigger sync via offline→online transition
    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // ── After first chunk POST is pending ──
    // setChunkProgress({current:1, total:3}) was called before the await
    expect(result.current.chunkProgress).toEqual({ current: 1, total: 3 });
    expect(result.current.syncProgress).toEqual({ current: 1, total: 3, items: 50 });
    expect(result.current.isSyncing).toBe(true);

    // ── Resolve chunk 1 → chunk 2 starts ──
    resolveChunk1();
    await flushMicrotasks();

    expect(result.current.chunkProgress).toEqual({ current: 2, total: 3 });
    expect(result.current.syncProgress).toEqual({ current: 2, total: 3, items: 50 });

    // ── Resolve chunk 2 → chunk 3 starts (last chunk: 20 remaining) ──
    resolveChunk2();
    await flushMicrotasks();

    expect(result.current.chunkProgress).toEqual({ current: 3, total: 3 });
    expect(result.current.syncProgress).toEqual({ current: 3, total: 3, items: 20 });

    // ── Resolve chunk 3 → sync completes ──
    resolveChunk3();
    await flushMicrotasks();

    // Progress cleared in finally block
    expect(result.current.isSyncing).toBe(false);
    expect(result.current.chunkProgress).toBeNull();
    expect(result.current.syncProgress).toBeNull();

    // All 120 marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(120);
    expect(mockAxiosPost).toHaveBeenCalledTimes(3);
  });
});
