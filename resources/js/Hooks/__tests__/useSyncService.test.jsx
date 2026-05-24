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

// A mutable object so tests can control isOnline
const { networkStatus } = vi.hoisted(() => ({ networkStatus: { isOnline: false } }));

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

// ── Helpers ───────────────────────────────────────────

/**
 * Create `count` pending order objects as returned by Dexie's .toArray().
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
 * Reliable because setTimeout(0) fires only after the microtask queue
 * (Promise .then callbacks) is fully drained.
 */
function flushMicrotasks() {
  return act(() => new Promise((resolve) => setTimeout(resolve, 0)));
}

// ── Tests ─────────────────────────────────────────────

describe('useSyncService', () => {
  beforeEach(() => {
    networkStatus.isOnline = false;
    vi.clearAllMocks();
    mockGetQueueCount.mockResolvedValue(0);
  });

  // ──────────────────────────────────────────────────────
  // 1. Auto-sync triggers when coming back online
  // ──────────────────────────────────────────────────────

  it('test_auto_sync_triggers_on_reconnect', async () => {
    const orders = createOrders(2);
    mockToArray.mockResolvedValue(orders);
    mockAxiosPost.mockResolvedValue({
      data: {
        synced: [
          { localUuid: 'uuid-1', serverOrderCode: 'ORD-001', serverId: 1 },
          { localUuid: 'uuid-2', serverOrderCode: 'ORD-002', serverId: 2 },
        ],
        failed: [],
        summary: { total: 2, synced: 2, failed: 0 },
      },
    });

    const { result, rerender } = renderHook(() => useSyncService());

    // Initially offline
    expect(result.current.isSyncing).toBe(false);
    expect(result.current.sessionExpired).toBe(false);

    // Switch to online → auto-sync fires
    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // POST called with all orders
    expect(mockAxiosPost).toHaveBeenCalledTimes(1);
    expect(mockAxiosPost).toHaveBeenCalledWith(
      '/api/sync-orders',
      { orders: orders.map((o) => JSON.parse(o.payload)) },
    );

    // Both marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(2);
    expect(mockMarkSynced).toHaveBeenCalledWith(1, 'ORD-001', 1);
    expect(mockMarkSynced).toHaveBeenCalledWith(2, 'ORD-002', 2);

    // State returns to idle
    expect(result.current.isSyncing).toBe(false);
  });

  // ──────────────────────────────────────────────────────
  // 2. Sequential sync — all orders in one batch POST
  // ──────────────────────────────────────────────────────

  it('test_sequential_sync', async () => {
    const orders = createOrders(3);
    mockToArray.mockResolvedValue(orders);
    mockAxiosPost.mockResolvedValue({
      data: {
        synced: [
          { localUuid: 'uuid-1', serverOrderCode: 'ORD-001', serverId: 1 },
          { localUuid: 'uuid-2', serverOrderCode: 'ORD-002', serverId: 2 },
          { localUuid: 'uuid-3', serverOrderCode: 'ORD-003', serverId: 3 },
        ],
        failed: [],
        summary: { total: 3, synced: 3, failed: 0 },
      },
    });

    const { rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // Single batch POST
    expect(mockAxiosPost).toHaveBeenCalledTimes(1);
    expect(mockAxiosPost).toHaveBeenCalledWith(
      '/api/sync-orders',
      { orders: orders.map((o) => JSON.parse(o.payload)) },
    );

    // All 3 marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(3);
  });

  // ──────────────────────────────────────────────────────
  // 3. Partial failure — failed orders don't block sync
  // ──────────────────────────────────────────────────────

  it('test_partial_failure_continues', async () => {
    const orders = createOrders(3);
    mockToArray.mockResolvedValue(orders);
    mockAxiosPost.mockResolvedValue({
      data: {
        synced: [
          { localUuid: 'uuid-1', serverOrderCode: 'ORD-001', serverId: 1 },
          { localUuid: 'uuid-3', serverOrderCode: 'ORD-003', serverId: 3 },
        ],
        failed: [
          { localUuid: 'uuid-2', reason: 'Menu tidak tersedia' },
        ],
        summary: { total: 3, synced: 2, failed: 1 },
      },
    });

    const { rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // markSynced called for orders 1 and 3
    expect(mockMarkSynced).toHaveBeenCalledTimes(2);
    expect(mockMarkSynced).toHaveBeenCalledWith(1, 'ORD-001', 1);
    expect(mockMarkSynced).toHaveBeenCalledWith(3, 'ORD-003', 3);

    // markFailed called for order 2
    expect(mockMarkFailed).toHaveBeenCalledTimes(1);
    expect(mockMarkFailed).toHaveBeenCalledWith(
      2,
      expect.stringContaining('Menu tidak tersedia'),
    );

    // Single POST call
    expect(mockAxiosPost).toHaveBeenCalledTimes(1);
  });

  // ──────────────────────────────────────────────────────
  // 4. Session expired (401) — sync halts
  // ──────────────────────────────────────────────────────

  it('test_session_expired_stops_sync', async () => {
    const orders = createOrders(2);
    mockToArray.mockResolvedValue(orders);

    const axiosError = new Error('Unauthorized');
    axiosError.isAxiosError = true;
    axiosError.response = { status: 401 };
    mockAxiosPost.mockRejectedValue(axiosError);

    const { result, rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // sessionExpired flag set
    expect(result.current.sessionExpired).toBe(true);

    // isSyncing returned to false (error handled gracefully)
    expect(result.current.isSyncing).toBe(false);

    // No orders marked (sync stopped)
    expect(mockMarkSynced).not.toHaveBeenCalled();
    expect(mockMarkFailed).not.toHaveBeenCalled();
  });

  // ──────────────────────────────────────────────────────
  // 5. Sync result summary
  // ──────────────────────────────────────────────────────

  it('test_sync_result_summary', async () => {
    const orders = createOrders(3);
    mockToArray.mockResolvedValue(orders);

    const responseData = {
      synced: [
        { localUuid: 'uuid-1', serverOrderCode: 'ORD-001', serverId: 1 },
        { localUuid: 'uuid-2', serverOrderCode: 'ORD-002', serverId: 2 },
      ],
      failed: [
        { localUuid: 'uuid-3', reason: 'Gagal' },
      ],
      summary: { total: 3, synced: 2, failed: 1 },
    };
    mockAxiosPost.mockResolvedValue({ data: responseData });

    const { result, rerender } = renderHook(() => useSyncService());

    networkStatus.isOnline = true;
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // lastSyncResult populated
    expect(result.current.lastSyncResult).toEqual(responseData);
    expect(result.current.lastSyncResult.summary.synced).toBe(2);
    expect(result.current.lastSyncResult.summary.failed).toBe(1);
  });

  // ──────────────────────────────────────────────────────
  // 6. No auto-sync when staying online
  // ──────────────────────────────────────────────────────

  it('test_sync_only_triggers_when_coming_online', async () => {
    networkStatus.isOnline = true;

    const orders = createOrders(2);
    mockToArray.mockResolvedValue(orders);

    const { rerender } = renderHook(() => useSyncService());

    // Flush initial pending-count effect
    await flushMicrotasks();

    // Re-render while still online — no state change
    await act(() => {
      rerender();
    });
    await flushMicrotasks();

    // POST should NOT have been called at any point
    expect(mockAxiosPost).not.toHaveBeenCalled();
  });

  // ──────────────────────────────────────────────────────
  // 7. Manual syncNow() trigger
  // ──────────────────────────────────────────────────────

  it('test_manual_sync_now_triggers', async () => {
    const orders = createOrders(2);
    mockToArray.mockResolvedValue(orders);
    mockAxiosPost.mockResolvedValue({
      data: {
        synced: [
          { localUuid: 'uuid-1', serverOrderCode: 'ORD-001', serverId: 1 },
          { localUuid: 'uuid-2', serverOrderCode: 'ORD-002', serverId: 2 },
        ],
        failed: [],
        summary: { total: 2, synced: 2, failed: 0 },
      },
    });

    const { result } = renderHook(() => useSyncService());

    // Manually invoke sync
    await act(() => {
      result.current.syncNow();
    });
    await flushMicrotasks();

    // POST called
    expect(mockAxiosPost).toHaveBeenCalledTimes(1);
    expect(mockAxiosPost).toHaveBeenCalledWith(
      '/api/sync-orders',
      expect.any(Object),
    );

    // Orders marked synced
    expect(mockMarkSynced).toHaveBeenCalledTimes(2);
  });
});
