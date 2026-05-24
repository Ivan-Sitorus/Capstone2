import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { db } from '@/db/dexie-config';
import { saveStockSnapshot, getAllStock, getStock } from '@/db/stockStore';

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

describe('stockStore', () => {

    // ── Test 1: saveStockSnapshot stores stock data correctly ────────────
    it('test_saveStockSnapshot_stores_menu_stock_data', async () => {
        const menus = [
            { id: 1, name: 'Kopi Robusta', stock: 95, is_stock_calculated: true },
            { id: 2, name: 'Teh Manis', stock: 50, is_stock_calculated: true },
            { id: 3, name: 'Roti Bakar', stock: 0, is_stock_calculated: true },
        ];

        await saveStockSnapshot(menus);

        const all = await db.stockSnapshots.toArray();
        expect(all).toHaveLength(3);

        const menu1 = all.find(s => s.menuId === 1);
        expect(menu1).toBeDefined();
        expect(menu1.stock).toBe(95);
        expect(menu1.isUnlimited).toBe(false);
        expect(typeof menu1.updatedAt).toBe('string');

        const menu2 = all.find(s => s.menuId === 2);
        expect(menu2).toBeDefined();
        expect(menu2.stock).toBe(50);

        const menu3 = all.find(s => s.menuId === 3);
        expect(menu3).toBeDefined();
        expect(menu3.stock).toBe(0);

        // Verify isUnlimited is true for very large stock values
        const menusUnlimited = [
            { id: 4, name: 'Air Mineral', stock: 999999, is_stock_calculated: false },
        ];
        await saveStockSnapshot(menusUnlimited);
        const unlimited = await db.stockSnapshots.get(4);
        expect(unlimited.isUnlimited).toBe(true);
    });

    // ── Test 2: getStock returns correct stock value ────────────────────
    it('test_getStock_returns_correct_stock', async () => {
        const menus = [
            { id: 1, name: 'Kopi Robusta', stock: 95, is_stock_calculated: true },
            { id: 2, name: 'Teh Manis', stock: 50, is_stock_calculated: true },
        ];

        await saveStockSnapshot(menus);

        const stock1 = await getStock(1);
        expect(stock1).toBe(95);

        const stock2 = await getStock(2);
        expect(stock2).toBe(50);

        // getStock returns -1 for non-existent menuId
        const stock3 = await getStock(999);
        expect(stock3).toBe(-1);
    });

    // ── Test 3: Second call overwrites previous data (upsert) ───────────
    it('test_saveStockSnapshot_overwrites_previous_data', async () => {
        // First save: 3 menus
        const menus1 = [
            { id: 1, name: 'Kopi Robusta', stock: 95, is_stock_calculated: true },
            { id: 2, name: 'Teh Manis', stock: 50, is_stock_calculated: true },
            { id: 3, name: 'Roti Bakar', stock: 0, is_stock_calculated: true },
        ];
        await saveStockSnapshot(menus1);

        // Verify all 3 exist
        expect(await db.stockSnapshots.count()).toBe(3);

        // Second save: 2 menus (overwrite IDs 1 & 2, add new ID 4)
        const menus2 = [
            { id: 1, name: 'Kopi Robusta', stock: 80, is_stock_calculated: true },
            { id: 4, name: 'Matcha Latte', stock: 20, is_stock_calculated: true },
        ];
        await saveStockSnapshot(menus2);

        // Total should be 4: ID 1 updated, ID 2 unchanged, ID 3 unchanged, ID 4 added
        expect(await db.stockSnapshots.count()).toBe(4);

        // Verify ID 1 was overwritten (stock 95 → 80)
        const updatedMenu1 = await db.stockSnapshots.get(1);
        expect(updatedMenu1.stock).toBe(80);

        // Verify ID 2 is still there with original stock
        const menu2 = await db.stockSnapshots.get(2);
        expect(menu2.stock).toBe(50);

        // Verify ID 4 is newly added
        const menu4 = await db.stockSnapshots.get(4);
        expect(menu4).toBeDefined();
        expect(menu4.stock).toBe(20);

        // getAllStock returns correct mapping after overwrite
        const allStock = await getAllStock();
        expect(allStock[1]).toBe(80);
        expect(allStock[2]).toBe(50);
        expect(allStock[3]).toBe(0);
        expect(allStock[4]).toBe(20);
        expect(Object.keys(allStock)).toHaveLength(4);
    });
});
