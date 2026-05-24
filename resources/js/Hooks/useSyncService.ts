import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';
import { useNetworkStatus } from '@/Hooks/useNetworkStatus';
import { db } from '@/db/dexie-config';
import { getQueueCount, markSynced, markFailed, clearSynced } from '@/db/offlineOrderStore';
import { SYNC_CONFIG } from '@/config/sync';
import type { SyncResponse, PendingOrder } from '@/types/offline';

const MAX_RETRIES = 3;
const COUNT_POLL_MS = 5_000;

// ── Chunk sizing ────────────────────────────────────────────────────

function calcChunkSize(): number {
  const budget =
    SYNC_CONFIG.vercelTimeoutMs -
    SYNC_CONFIG.phpColdStartMs -
    SYNC_CONFIG.safetyMarginMs;
  const chunk = Math.floor(budget / SYNC_CONFIG.timePerOrderMs);
  return Math.max(SYNC_CONFIG.minChunkSize, Math.min(chunk, SYNC_CONFIG.maxChunkSize));
}

interface SyncProgress {
  current: number;
  total: number;
  items: number;
}

interface ChunkProgress {
  current: number;
  total: number;
}

interface UseSyncServiceReturn {
  isSyncing: boolean;
  lastSyncResult: SyncResponse | null;
  pendingCount: number;
  syncProgress: SyncProgress | null;
  chunkProgress: ChunkProgress | null;
  syncNow: () => Promise<void>;
  sessionExpired: boolean;
}

export function useSyncService(): UseSyncServiceReturn {
  const { isOnline } = useNetworkStatus();
  const [isSyncing, setIsSyncing] = useState(false);
  const [lastSyncResult, setLastSyncResult] = useState<SyncResponse | null>(null);
  const [pendingCount, setPendingCount] = useState(0);
  const [syncProgress, setSyncProgress] = useState<SyncProgress | null>(null);
  const [chunkProgress, setChunkProgress] = useState<ChunkProgress | null>(null);
  const [sessionExpired, setSessionExpired] = useState(false);

  // ── Guards ──
  const wasOfflineRef = useRef(!isOnline);
  const isSyncingRef = useRef(false);
  const unmountedRef = useRef(false);
  const retryCountsRef = useRef<Map<number, number>>(new Map()); // localId → count

  // ── Periodic pending-count poll ──
  useEffect(() => {
    const updateCount = async () => {
      const count = await getQueueCount();
      if (!unmountedRef.current) setPendingCount(count);
    };
    updateCount();
    const interval = setInterval(updateCount, COUNT_POLL_MS);
    return () => {
      unmountedRef.current = true;
      clearInterval(interval);
    };
  }, []);

  // ── Core sync ─────────────────────────────────────────────────────
  const syncNow = useCallback(async (): Promise<void> => {
    // Debounce: skip if already syncing
    if (isSyncingRef.current) return;
    if (unmountedRef.current) return;

    isSyncingRef.current = true;
    setIsSyncing(true);
    setSyncProgress(null);
    setSessionExpired(false);

    try {
      // Fetch all retryable orders (pending_sync + previously failed within retry limit)
      const allRetryable = await db.offlineOrders
        .where('status')
        .anyOf(['pending_sync', 'failed'])
        .toArray();

      if (allRetryable.length === 0) {
        setPendingCount(0);
        return;
      }

      const retryMap = retryCountsRef.current;
      const ordersToSync: (PendingOrder & { parsedPayload: Record<string, unknown> })[] = [];

      // Cull orders that exceeded max retries
      for (const order of allRetryable) {
        const currentRetries = retryMap.get(order.localId) ?? 0;
        if (currentRetries >= MAX_RETRIES) {
          // Permanently fail — only write if not already marked failed with this reason
          if (order.status !== 'failed' || !order.error?.includes('Max retries')) {
            await markFailed(order.localId, `Max retries (${MAX_RETRIES}) exceeded`);
          }
          retryMap.delete(order.localId);
          continue;
        }
        ordersToSync.push({ ...order, parsedPayload: JSON.parse(order.payload) });
      }

      if (ordersToSync.length === 0) {
        setPendingCount(await getQueueCount());
        return;
      }

      // Build batch request — strip localId from payload, map uuid → localId
      const uuidToLocalId = new Map<string, number>();
      const requestOrders = ordersToSync.map((o) => {
        uuidToLocalId.set(o.parsedPayload.uuid as string, o.localId);
        return {
          ...o.parsedPayload,
          items: (o.parsedPayload.items as Array<Record<string, unknown>>).map((item: Record<string, unknown>) => ({
            menu_id: item.menuId,
            quantity: item.qty,
            price: item.price,
          })),
        };
      });

      setSyncProgress(null);
      setChunkProgress(null);

      // ── Chunked POST loop ──────────────────────────────────────────
      const chunkSize = calcChunkSize();
      const totalChunks = Math.ceil(requestOrders.length / chunkSize);

      let accumulated: SyncResponse | null = null;

      for (let i = 0; i < requestOrders.length; i += chunkSize) {
        const chunk = requestOrders.slice(i, i + chunkSize);
        const currentChunk = Math.floor(i / chunkSize) + 1;

        setSyncProgress({ current: currentChunk, total: totalChunks, items: chunk.length });
        setChunkProgress({ current: currentChunk, total: totalChunks });

        try {
          const response = await axios.post<SyncResponse>('/sync-orders', {
            orders: chunk,
          });

          const { synced, failed } = response.data;

          // ── Process successes ──
          for (const s of synced) {
            const localId = uuidToLocalId.get(s.localUuid);
            if (localId !== undefined) {
              await markSynced(localId, s.serverOrderCode, s.serverId);
              retryMap.delete(localId);
            }
          }

          // ── Process failures ──
          for (const f of failed) {
            const localId = uuidToLocalId.get(f.localUuid);
            if (localId !== undefined) {
              const nextRetry = (retryMap.get(localId) ?? 0) + 1;
              await markFailed(
                localId,
                `Attempt ${nextRetry}/${MAX_RETRIES}: ${f.reason}`,
              );
              if (nextRetry < MAX_RETRIES) {
                retryMap.set(localId, nextRetry);
              } else {
                await markFailed(
                  localId,
                  `Max retries (${MAX_RETRIES}) exceeded: ${f.reason}`,
                );
                retryMap.delete(localId);
              }
            }
          }

          // Accumulate last successful response
          accumulated = response.data;
        } catch (err: unknown) {
          // ── Catastrophic errors (network, auth) ──
          if (axios.isAxiosError(err)) {
            if (err.response?.status === 401) {
              setSessionExpired(true);
              break;
            } else if (err.response?.status === 419) {
              window.location.reload();
              break;
            }
          }
          // Network or other errors — stop chunking; retry on next online transition
          break;
        }
      }

      // ── Cleanup & reload after full sync ──
      if (!unmountedRef.current) {
        await clearSynced(0);
      }
      setTimeout(() => {
        if (!unmountedRef.current) {
          router.reload({ only: ['categories', 'pendingOrderCount'], preserveState: true, preserveScroll: true });
        }
      }, 500);

      if (accumulated) {
        setLastSyncResult(accumulated);
      }
    } catch (_err: unknown) {
      // Non-chunk errors (culling, JSON.parse, etc.) — silently skip;
      // the finally block will reset state for the next sync cycle.
    } finally {
      if (!unmountedRef.current) {
        setPendingCount(await getQueueCount());
        setSyncProgress(null);
        setChunkProgress(null);
        setIsSyncing(false);
        isSyncingRef.current = false;
      }
    }
  }, []);

  // ── Auto-sync on offline→online transition ────────────────────────
  useEffect(() => {
    if (isOnline && wasOfflineRef.current) {
      wasOfflineRef.current = false;
      syncNow();
    }
    if (!isOnline) {
      wasOfflineRef.current = true;
    }
  }, [isOnline, syncNow]);

  return {
    isSyncing,
    lastSyncResult,
    pendingCount,
    syncProgress,
    chunkProgress,
    syncNow,
    sessionExpired,
  };
}
