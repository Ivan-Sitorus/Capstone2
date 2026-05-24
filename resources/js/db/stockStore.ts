import { db } from './dexie-config';

export async function saveStockSnapshot(
  menus: Array<{ id: number; stock?: number; is_stock_calculated?: boolean }>,
): Promise<void> {
  await db.transaction('rw', db.stockSnapshots, async () => {
    for (const menu of menus) {
      await db.stockSnapshots.put({
        menuId: menu.id,
        stock: menu.stock ?? 0,
        isUnlimited: (menu.stock ?? 0) >= 999999,
        updatedAt: new Date().toISOString(),
      });
    }
  });
}

export async function getAllStock(): Promise<Record<number, number>> {
  const snapshots = await db.stockSnapshots.toArray();
  const result: Record<number, number> = {};
  for (const s of snapshots) {
    result[s.menuId] = s.stock;
  }
  return result;
}

export async function getStock(menuId: number): Promise<number> {
  const snapshot = await db.stockSnapshots.get(menuId);
  return snapshot ? snapshot.stock : -1;
}
