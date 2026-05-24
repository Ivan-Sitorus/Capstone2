import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { db } from '@/db/dexie-config';
import {
    saveOrder,
    getPendingOrders,
    getQueueCount,
    getUnsyncedOrdersCount,
    markSynced,
    markFailed,
    getStorageUsage,
    clearSynced,
    MaxQueueExceededError,
} from '@/db/offlineOrderStore';

// ── Helpers ────────────────────────────────────────────────────────────

const makePayload = (overrides = {}) => ({
    uuid: crypto.randomUUID(),
    items: [
        { menuId: 1, name: 'Kopi Robusta', qty: 2, price: 12000, subtotal: 24000 },
        { menuId: 2, name: 'Roti Bakar', qty: 1, price: 15000, subtotal: 15000 },
    ],
    paymentMethod: 'cash',
    customerName: 'Test Customer',
    isMahasiswa: false,
    total: 39000,
    createdAt: new Date().toISOString(),
    ...overrides,
});

// ── Database lifecycle ─────────────────────────────────────────────────

beforeEach(async () => {
    await db.delete();
    await db.open();
});

afterEach(async () => {
    await db.delete();
    await db.open();
});

// ── Test suite ─────────────────────────────────────────────────────────

describe('offlineOrderStore', () => {
    // ── Test 1: saveOrder stores in dexie ───────────────────────────────
    it('test_saveOrder_stores_in_dexie', async () => {
        const payload = makePayload();
        const localId = await saveOrder(payload);

        // Verify localId is a number
        expect(localId).toBeGreaterThan(0);
        expect(Number.isInteger(localId)).toBe(true);

        // Verify order record in offlineOrders table
        const order = await db.offlineOrders.get(localId);
        expect(order).not.toBeNull();
        expect(order.uuid).toBe(payload.uuid);
        expect(order.status).toBe('pending_sync');
        expect(order.error).toBeNull();
        expect(order.createdAt).toBe(payload.createdAt);

        // Verify items in offlineOrderItems table
        const items = await db.offlineOrderItems
            .where('orderLocalId')
            .equals(localId)
            .toArray();

        expect(items).toHaveLength(2);
        expect(items[0].menuId).toBe(1);
        expect(items[0].name).toBe('Kopi Robusta');
        expect(items[0].qty).toBe(2);
        expect(items[0].price).toBe(12000);
        expect(items[0].subtotal).toBe(24000);
        expect(items[1].menuId).toBe(2);
        expect(items[1].name).toBe('Roti Bakar');
        expect(items[1].qty).toBe(1);
    });

    // ── Test 2: saveOrder atomic transaction ───────────────────────────
    it('test_saveOrder_atomic_transaction', async () => {
        // First save — should succeed
        const payload1 = makePayload();
        const localId1 = await saveOrder(payload1);
        expect(localId1).toBeGreaterThan(0);

        // Second save with DUPLICATE uuid — should fail due to unique constraint
        const duplicatePayload = makePayload({ uuid: payload1.uuid });
        await expect(saveOrder(duplicatePayload)).rejects.toThrow();

        // Verify ONLY one order exists (second was rolled back atomically)
        const allOrders = await db.offlineOrders.toArray();
        expect(allOrders).toHaveLength(1);
        expect(allOrders[0].localId).toBe(localId1);

        // Verify items from FIRST order are intact (not rolled back)
        const items = await db.offlineOrderItems
            .where('orderLocalId')
            .equals(localId1)
            .toArray();
        expect(items).toHaveLength(2);
    });

    // ── Test 3: getPendingOrders returns only pending ──────────────────
    it('test_getPendingOrders_returns_only_pending', async () => {
        // Insert orders with mixed statuses
        const pending1 = makePayload();
        const pending2 = makePayload();
        const syncPayload = makePayload();

        const localId1 = await saveOrder(pending1);
        const localId2 = await saveOrder(pending2);
        const localId3 = await saveOrder(syncPayload);

        // Mark one as synced
        await markSynced(localId3, 'ORD-SYNCED-001', 1);

        // Mark another as failed
        const failedPayload = makePayload();
        const localId4 = await saveOrder(failedPayload);
        await markFailed(localId4, 'Network error: timeout');

        // getPendingOrders should return only pending_sync
        const pending = await getPendingOrders();

        expect(pending).toHaveLength(2);

        const pendingIds = pending.map(o => o.localId);
        expect(pendingIds).toContain(localId1);
        expect(pendingIds).toContain(localId2);
        expect(pendingIds).not.toContain(localId3); // synced
        expect(pendingIds).not.toContain(localId4); // failed

        // Verify all returned have status 'pending_sync'
        pending.forEach(o => {
            expect(o.status).toBe('pending_sync');
        });
    });

    // ── Test 4: markSynced updates status ──────────────────────────────
    it('test_markSynced_updates_status', async () => {
        const payload = makePayload();
        const localId = await saveOrder(payload);

        await markSynced(localId, 'ORD-20260519-0001', 99);

        // Verify status changed to synced
        const order = await db.offlineOrders.get(localId);
        expect(order.status).toBe('synced');

        // Verify it no longer appears in pending orders
        const pending = await getPendingOrders();
        expect(pending).toHaveLength(0);

        // Verify payload was enriched with server info
        const parsed = JSON.parse(order.payload);
        expect(parsed.serverOrderCode).toBe('ORD-20260519-0001');
        expect(parsed.serverId).toBe(99);

        // Verify getUnsyncedOrdersCount returns 0
        const count = await getUnsyncedOrdersCount();
        expect(count).toBe(0);
    });

    // ── Test 5: markFailed stores error ────────────────────────────────
    it('test_markFailed_stores_error', async () => {
        const payload = makePayload();
        const localId = await saveOrder(payload);

        const errorReason = 'NetworkError: Failed to fetch';
        await markFailed(localId, errorReason);

        // Verify status changed to failed
        const order = await db.offlineOrders.get(localId);
        expect(order.status).toBe('failed');
        expect(order.error).toBe(errorReason);

        // Verify it no longer appears in pending orders
        const pending = await getPendingOrders();
        expect(pending).toHaveLength(0);
    });

    // ── Test 6: getQueueCount counts correctly ─────────────────────────
    it('test_getQueueCount_counts_correctly', async () => {
        // Initially 0
        expect(await getQueueCount()).toBe(0);

        // Add 3 orders
        await saveOrder(makePayload());
        await saveOrder(makePayload());
        await saveOrder(makePayload());

        expect(await getQueueCount()).toBe(3);

        // Mark one as synced — count should decrease
        const allPending = await getPendingOrders();
        await markSynced(allPending[0].localId, 'ORD-DONE', 1);

        expect(await getQueueCount()).toBe(2);
    });

    // ── Test 7: max queue enforcement ──────────────────────────────────
    it('test_max_queue_enforcement', async () => {
        // Fill queue to MAX (50)
        for (let i = 0; i < 50; i++) {
            await saveOrder(makePayload());
        }

        expect(await getQueueCount()).toBe(50);

        // 51st should throw MaxQueueExceededError
        await expect(saveOrder(makePayload())).rejects.toThrow(MaxQueueExceededError);

        // Verify queue is still at 50 (no 51st order added)
        expect(await getQueueCount()).toBe(50);
    });

    // ── Test 8: clearSynced removes old entries ────────────────────────
    it('test_clearSynced_removes_old_entries', async () => {
        // Create an order and immediately mark as synced
        const oldPayload = makePayload({
            createdAt: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString(), // 48h ago
        });
        const oldId = await saveOrder(oldPayload);
        await markSynced(oldId, 'ORD-OLD', 1);

        // Create a recent synced order (should NOT be removed)
        const recentPayload = makePayload({
            createdAt: new Date().toISOString(), // just now
        });
        const recentId = await saveOrder(recentPayload);
        await markSynced(recentId, 'ORD-RECENT', 2);

        // Create a pending order (should NOT be affected)
        const pendingId = await saveOrder(makePayload());

        // Verify all three exist
        expect(await db.offlineOrders.count()).toBe(3);

        // Clear synced orders older than 24h (default)
        const removed = await clearSynced();

        expect(removed).toBe(1);

        // Old synced order should be gone
        const oldOrder = await db.offlineOrders.get(oldId);
        expect(oldOrder).toBeUndefined();

        // Recent synced should still exist
        const recentOrder = await db.offlineOrders.get(recentId);
        expect(recentOrder).not.toBeUndefined();
        expect(recentOrder.status).toBe('synced');

        // Pending should still exist
        const pendingOrder = await db.offlineOrders.get(pendingId);
        expect(pendingOrder).not.toBeUndefined();
        expect(pendingOrder.status).toBe('pending_sync');

        // Items associated with removed order should also be deleted
        const items = await db.offlineOrderItems
            .where('orderLocalId')
            .equals(oldId)
            .toArray();
        expect(items).toHaveLength(0);

        // Items of recent order should still exist
        const recentItems = await db.offlineOrderItems
            .where('orderLocalId')
            .equals(recentId)
            .toArray();
        expect(recentItems).toHaveLength(2);
    });

    // ── Test 9: storage quota tracking ─────────────────────────────────
    it('test_storage_quota_tracking', async () => {
        // In jsdom + fake-indexeddb, navigator.storage.estimate may not be available.
        // The implementation handles this gracefully.
        const result = await getStorageUsage();

        // Verify return type structure
        expect(result).toHaveProperty('usage');
        expect(result).toHaveProperty('quota');
        expect(result).toHaveProperty('percentUsed');

        // Type checks
        expect(typeof result.usage).toBe('number');
        expect(typeof result.quota).toBe('number');
        expect(typeof result.percentUsed).toBe('number');

        // Values should be non-negative
        expect(result.usage).toBeGreaterThanOrEqual(0);
        expect(result.quota).toBeGreaterThanOrEqual(0);
        expect(result.percentUsed).toBeGreaterThanOrEqual(0);
    });
});
