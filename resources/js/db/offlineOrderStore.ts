import { db } from './dexie-config';
import type { OfflineOrderPayload } from '@/types/offline';

const MAX_PENDING = 50;

export class MaxQueueExceededError extends Error {
  constructor() {
    super(`Queue is full: maximum ${MAX_PENDING} pending orders`);
    this.name = 'MaxQueueExceededError';
  }
}

export class StorageQuotaExceededError extends Error {
  constructor() {
    super('Storage quota exceeded — cannot save offline order');
    this.name = 'StorageQuotaExceededError';
  }
}

function isQuotaError(err: unknown): boolean {
  if (err instanceof Error) return /quota|no space|no remaining/i.test(err.message);
  if (err && typeof err === 'object' && 'message' in err) {
    return /quota|no space|no remaining/i.test(String(err.message));
  }
  return false;
}

function enrichPayload(payload: OfflineOrderPayload, extras: Record<string, unknown>): string {
  return JSON.stringify({ ...JSON.parse(JSON.stringify(payload)), ...extras });
}

export async function saveOrder(payload: OfflineOrderPayload): Promise<number> {
  const pendingCount = await db.offlineOrders
    .where('status')
    .equals('pending_sync')
    .count();

  if (pendingCount >= MAX_PENDING) {
    throw new MaxQueueExceededError();
  }

  try {
    return await db.transaction(
      'rw',
      [db.offlineOrders, db.offlineOrderItems],
      async () => {
        const localId = await db.offlineOrders.add({
          uuid: payload.uuid,
          payload: JSON.stringify(payload),
          status: 'pending_sync',
          error: null,
          createdAt: payload.createdAt,
        });

        const items = payload.items.map(item => ({
          orderLocalId: localId,
          menuId: item.menuId,
          name: item.name,
          qty: item.qty,
          price: item.price,
          subtotal: item.subtotal,
        }));

        await db.offlineOrderItems.bulkAdd(items);

        return localId;
      },
    );
  } catch (err: unknown) {
    if (isQuotaError(err)) throw new StorageQuotaExceededError();
    throw err;
  }
}

export async function getPendingOrders() {
  return db.offlineOrders
    .where('status')
    .equals('pending_sync')
    .sortBy('createdAt');
}

export function getUnsyncedOrdersCount() {
  return db.offlineOrders
    .where('status')
    .equals('pending_sync')
    .count();
}

export async function markSynced(
  localId: number,
  serverOrderCode: string,
  serverId: number,
): Promise<void> {
  await db.transaction('rw', db.offlineOrders, async () => {
    const order = await db.offlineOrders.get(localId);
    if (!order) return;

    const payload = enrichPayload(JSON.parse(order.payload), {
      serverOrderCode,
      serverId,
    });

    await db.offlineOrders.update(localId, {
      status: 'synced',
      payload,
    });
  });
}

export async function markFailed(localId: number, reason: string): Promise<void> {
  await db.offlineOrders.update(localId, {
    status: 'failed',
    error: reason,
  });
}

export function getQueueCount() {
  return db.offlineOrders
    .where('status')
    .equals('pending_sync')
    .count();
}

export async function getStorageUsage(): Promise<{
  usage: number;
  quota: number;
  percentUsed: number;
}> {
  if (typeof navigator?.storage?.estimate !== 'function') {
    return { usage: 0, quota: 0, percentUsed: 0 };
  }

  const { usage, quota } = await navigator.storage.estimate();
  return {
    usage: usage ?? 0,
    quota: quota ?? 0,
    percentUsed: quota ? ((usage ?? 0) / quota) * 100 : 0,
  };
}

export async function clearSynced(maxAgeMs = 24 * 60 * 60 * 1000): Promise<number> {
  const cutoff = new Date(Date.now() - maxAgeMs).toISOString();

  const syncedOrders = await db.offlineOrders
    .where('status')
    .equals('synced')
    .filter(o => o.createdAt < cutoff)
    .toArray();

  if (syncedOrders.length === 0) return 0;

  const localIds = syncedOrders.map(o => o.localId);

  return db.transaction('rw', [db.offlineOrders, db.offlineOrderItems], async () => {
    await db.offlineOrderItems.where('orderLocalId').anyOf(localIds).delete();
    await db.offlineOrders.bulkDelete(localIds);
    return syncedOrders.length;
  });
}
