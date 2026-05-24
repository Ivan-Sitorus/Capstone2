export { db } from './dexie-config';
export type { OfflineOrder, OfflineOrderItem } from './dexie-config';
export {
  saveOrder,
  getPendingOrders,
  getUnsyncedOrdersCount,
  markSynced,
  markFailed,
  getQueueCount,
  getStorageUsage,
  clearSynced,
  MaxQueueExceededError,
  StorageQuotaExceededError,
} from './offlineOrderStore';
