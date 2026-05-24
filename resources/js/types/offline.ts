// Offline order data structures for PWA sync
// Types only — no runtime code, no imports from Inertia or Laravel models.

export interface OfflineOrderPayload {
  uuid: string;
  items: OfflineOrderItem[];
  paymentMethod: 'cash' | 'qris' | 'bayar_nanti';
  customerName: string | null;
  isMahasiswa: boolean;
  total: number;
  createdAt: string; // ISO date string
}

export interface OfflineOrderItem {
  menuId: number;
  name: string;
  qty: number;
  price: number;
  subtotal: number;
}

export interface SyncResult {
  localUuid: string;
  serverOrderCode: string;
  serverId: number;
}

export interface SyncFailed {
  localUuid: string;
  reason: string;
}

export interface SyncResponse {
  synced: SyncResult[];
  failed: SyncFailed[];
  summary: {
    total: number;
    synced: number;
    failed: number;
  };
}

export type NetworkStatus = 'online' | 'offline' | 'checking';

// Zustand cart item
export interface CartItem {
  menuId: number;
  name: string;
  price: number;
  qty: number;
  subtotal: number;
  categoryId?: number;
}

// Pending order from Dexie store
export interface PendingOrder {
  localId: number;
  uuid: string;
  payload: string;
  status: 'pending_sync' | 'synced' | 'failed';
  error: string | null;
  createdAt: string;
}

// Sync service state
export interface SyncState {
  isSyncing: boolean;
  lastSyncResult: SyncResponse | null;
  pendingCount: number;
}
