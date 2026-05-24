import Dexie, { type EntityTable } from 'dexie';

// ── Table row interfaces ──────────────────────────────────────────────

interface OfflineOrder {
  localId: number;
  uuid: string;
  payload: string; // JSON-serialized OfflineOrderPayload
  status: 'pending_sync' | 'synced' | 'failed';
  error: string | null;
  createdAt: string;
}

interface OfflineOrderItem {
  id?: number;
  orderLocalId: number;
  menuId: number;
  name: string;
  qty: number;
  price: number;
  subtotal: number;
}

interface CashierCartEntry {
  id?: number;
  items: Array<{ menuId: number; name: string; price: number; cashback?: number; quantity: number; image?: string | null }>;
  updatedAt: string;
}

interface StockSnapshot {
  menuId: number;
  stock: number;
  isUnlimited: boolean;
  updatedAt: string;
}

interface IngredientTotal {
  ingredientId: number;
  total: number;
}

interface MenuRecipe {
  menuId: number;
  recipe: Array<{ ingredientId: number; quantityUsed: number }>;
}

interface MenuStockTotal {
  menuId: number;
  quantity: number;
}

// ── Typed Dexie instance ──────────────────────────────────────────────

const db = new Dexie('w9cafe') as Dexie & {
  offlineOrders: EntityTable<OfflineOrder, 'localId'>;
  offlineOrderItems: EntityTable<OfflineOrderItem, 'id'>;
  cashierCart: EntityTable<CashierCartEntry, 'id'>;
  stockSnapshots: EntityTable<StockSnapshot, 'menuId'>;
  ingredientTotals: EntityTable<IngredientTotal, 'ingredientId'>;
  menuRecipes: EntityTable<MenuRecipe, 'menuId'>;
  menuStockTotals: EntityTable<MenuStockTotal, 'menuId'>;
};

db.version(1).stores({
  offlineOrders: '++localId, &uuid, status, createdAt',
  offlineOrderItems: '++id, orderLocalId, menuId',
});

db.version(2).stores({
  cashierCart: '++id, updatedAt',
});

db.version(3).stores({
  stockSnapshots: 'menuId, stock, isUnlimited, updatedAt',
});

db.version(4).stores({
  ingredientTotals: 'ingredientId, total',
  menuRecipes: 'menuId, recipe',
  menuStockTotals: 'menuId, quantity',
});

// ── Exports ───────────────────────────────────────────────────────────

export type { OfflineOrder, OfflineOrderItem, CashierCartEntry, StockSnapshot, IngredientTotal, MenuRecipe, MenuStockTotal };
export { db };
