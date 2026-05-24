import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import { db } from '@/db/dexie-config';
import { saveRawStockData } from '@/db/stockRawStore';
import { calculateMenuStock, processOfflineOrder, recalculateAllStocks } from '@/db/stockCalcService';
import { getStock } from '@/db/stockStore';

beforeEach(async () => {
  await db.delete();
  await db.open();
});

afterEach(async () => {
  await db.delete();
  await db.open();
});

describe('stockCalcService', () => {

  it('test_recipe_based_calculation', async () => {
    const categories = [
      {
        id: 1, name: 'Makanan', menus: [
          {
            id: 1, name: 'Mie Goreng',
            menu_ingredients: [
              {
                id: 1, menu_id: 1, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
              {
                id: 2, menu_id: 1, ingredient_id: 2, quantity_used: 50,
                ingredient: { id: 2, name: 'Minyak', batches: [{ id: 2, quantity: 500 }] },
              },
            ],
            menu_stock: null,
          },
        ],
      },
    ];

    await saveRawStockData(categories);

    const stock = await calculateMenuStock(1);
    // min(floor(1000/200), floor(500/50)) = min(5, 10) = 5
    expect(stock).toBe(5);
  });

  it('test_shared_ingredient_cascading', async () => {
    const categories = [
      {
        id: 1, name: 'Makanan', menus: [
          {
            id: 1, name: 'Mie Goreng',
            menu_ingredients: [
              {
                id: 1, menu_id: 1, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
            ],
            menu_stock: null,
          },
          {
            id: 2, name: 'Mie Rebus',
            menu_ingredients: [
              {
                id: 2, menu_id: 2, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
            ],
            menu_stock: null,
          },
        ],
      },
    ];

    await saveRawStockData(categories);

    // Both menus should have stock 5 initially
    expect(await calculateMenuStock(1)).toBe(5);
    expect(await calculateMenuStock(2)).toBe(5);

    // Process 3 Mie Goreng → uses 3 * 200 = 600g Mie → Mie left = 400g
    await processOfflineOrder([{ menuId: 1, qty: 3 }]);

    // Mie Rebus stock = floor(400/200) = 2 (was 5)
    // Mie Goreng stock = floor(400/200) = 2 (was 5)
    const mieGorengStock = await getStock(1);
    const mieRebusStock = await getStock(2);
    expect(mieGorengStock).toBe(2);
    expect(mieRebusStock).toBe(2);
  });

  it('test_non_recipe_stock', async () => {
    const categories = [
      {
        id: 1, name: 'Minuman', menus: [
          {
            id: 3, name: 'Air Mineral',
            menu_ingredients: [],
            menu_stock: {
              id: 1, menu_id: 3,
              batches: [{ id: 1, quantity: 30 }],
            },
          },
        ],
      },
    ];

    await saveRawStockData(categories);

    // Initial stock = 30
    expect(await calculateMenuStock(3)).toBe(30);

    // Process order of 5
    await processOfflineOrder([{ menuId: 3, qty: 5 }]);

    // Stock should be 25
    const stock = await getStock(3);
    expect(stock).toBe(25);
  });

  it('test_processOfflineOrder_decrements_and_recalculates', async () => {
    const categories = [
      {
        id: 1, name: 'Makanan', menus: [
          {
            id: 1, name: 'Mie Goreng',
            menu_ingredients: [
              {
                id: 1, menu_id: 1, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
              {
                id: 2, menu_id: 1, ingredient_id: 2, quantity_used: 50,
                ingredient: { id: 2, name: 'Minyak', batches: [{ id: 2, quantity: 500 }] },
              },
            ],
            menu_stock: null,
          },
          {
            id: 2, name: 'Mie Rebus',
            menu_ingredients: [
              {
                id: 3, menu_id: 2, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
            ],
            menu_stock: null,
          },
          {
            id: 3, name: 'Air Mineral',
            menu_ingredients: [],
            menu_stock: {
              id: 1, menu_id: 3,
              batches: [{ id: 1, quantity: 30 }],
            },
          },
        ],
      },
    ];

    await saveRawStockData(categories);

    // Process order: 2 Mie Goreng + 4 Air Mineral
    await processOfflineOrder([
      { menuId: 1, qty: 2 },
      { menuId: 3, qty: 4 },
    ]);

    // Mie total after order: 1000 - (2 * 200) = 600
    // Mie Goreng stock = min(floor(600/200), floor(500/50)) = min(3, 10) = 3
    const mieGorengStock = await getStock(1);
    expect(mieGorengStock).toBe(3);

    // Mie Rebus stock = floor(600/200) = 3
    const mieRebusStock = await getStock(2);
    expect(mieRebusStock).toBe(3);

    // Air Mineral stock = 30 - 4 = 26
    const airMineralStock = await getStock(3);
    expect(airMineralStock).toBe(26);
  });

  it('test_multiple_orders_accumulate', async () => {
    const categories = [
      {
        id: 1, name: 'Makanan', menus: [
          {
            id: 1, name: 'Mie Goreng',
            menu_ingredients: [
              {
                id: 1, menu_id: 1, ingredient_id: 1, quantity_used: 200,
                ingredient: { id: 1, name: 'Mie', batches: [{ id: 1, quantity: 1000 }] },
              },
            ],
            menu_stock: null,
          },
          {
            id: 3, name: 'Air Mineral',
            menu_ingredients: [],
            menu_stock: {
              id: 1, menu_id: 3,
              batches: [{ id: 1, quantity: 30 }],
            },
          },
        ],
      },
    ];

    await saveRawStockData(categories);

    // Order 1: 2 Mie Goreng
    await processOfflineOrder([{ menuId: 1, qty: 2 }]);
    // Mie: 1000 - 400 = 600 → Mie Goreng stock = floor(600/200) = 3
    expect(await getStock(1)).toBe(3);

    // Order 2: 1 Mie Goreng + 5 Air Mineral
    await processOfflineOrder([
      { menuId: 1, qty: 1 },
      { menuId: 3, qty: 5 },
    ]);
    // Mie: 600 - 200 = 400 → Mie Goreng stock = floor(400/200) = 2
    expect(await getStock(1)).toBe(2);
    // Air Mineral: 30 - 5 = 25
    expect(await getStock(3)).toBe(25);

    // Order 3: 2 Mie Goreng (but only 2 stock left)
    await processOfflineOrder([{ menuId: 1, qty: 2 }]);
    // Mie: 400 - 400 = 0 → Mie Goreng stock = floor(0/200) = 0
    expect(await getStock(1)).toBe(0);

    // Verify ingredient total is 0 (clamped, not negative)
    const miEntry = await db.ingredientTotals.get(1);
    expect(miEntry.total).toBe(0);
  });
});
