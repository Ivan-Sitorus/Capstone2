import { db } from './dexie-config';
import type { MenuRecipe, IngredientTotal, MenuStockTotal } from './dexie-config';

/**
 * Save raw stock data from server categories into ingredient-level stores.
 * Recipe-based menus → ingredientTotals + menuRecipes
 * Non-recipe menus → menuStockTotals
 */
export async function saveRawStockData(categories: Array<any>): Promise<void> {
  const ingredientTotalsMap: Record<number, number> = {};
  const recipes: MenuRecipe[] = [];
  const stockTotals: MenuStockTotal[] = [];

  for (const category of categories) {
    for (const menu of category.menus || []) {
      const ingredients = menu.menu_ingredients;

      if (ingredients && ingredients.length > 0) {
        // Recipe-based: extract ingredient batches and recipe
        const recipe: MenuRecipe['recipe'] = [];

        for (const mi of ingredients) {
          const ingredientId = mi.ingredient_id;
          const quantityUsed = mi.quantity_used;

          if (!ingredientId || !quantityUsed) continue;

          recipe.push({ ingredientId, quantityUsed });

          // Sum all batches for this ingredient
          if (mi.ingredient && mi.ingredient.batches) {
            const batchTotal = mi.ingredient.batches.reduce(
              (sum: number, b: any) => sum + (Number(b.quantity) || 0),
              0,
            );
            // Upsert: same ingredient may appear through different menus
            ingredientTotalsMap[ingredientId] = Math.max(
              ingredientTotalsMap[ingredientId] || 0,
              batchTotal,
            );
          }
        }

        recipes.push({ menuId: menu.id, recipe });
      } else {
        // Non-recipe: sum menuStock batches
        let qty = 0;
        if (menu.menu_stock && menu.menu_stock.batches) {
          qty = menu.menu_stock.batches.reduce(
            (sum: number, b: any) => sum + (Number(b.quantity) || 0),
            0,
          );
        }
        stockTotals.push({ menuId: menu.id, quantity: qty });
      }
    }
  }

  await db.transaction(
    'rw',
    [db.ingredientTotals, db.menuRecipes, db.menuStockTotals],
    async () => {
      // Clear and refill from fresh server data
      await db.ingredientTotals.clear();
      await db.menuRecipes.clear();
      await db.menuStockTotals.clear();

      for (const [ingredientId, total] of Object.entries(ingredientTotalsMap)) {
        await db.ingredientTotals.put({
          ingredientId: Number(ingredientId),
          total,
        });
      }

      for (const r of recipes) {
        await db.menuRecipes.put(r);
      }

      for (const s of stockTotals) {
        await db.menuStockTotals.put(s);
      }
    },
  );
}

export async function getIngredientTotal(
  ingredientId: number,
): Promise<number> {
  const entry = await db.ingredientTotals.get(ingredientId);
  return entry ? entry.total : 0;
}

export async function getMenuRecipe(
  menuId: number,
): Promise<Array<{ ingredientId: number; quantityUsed: number }>> {
  const entry = await db.menuRecipes.get(menuId);
  return entry ? entry.recipe : [];
}

export async function getMenuStockTotal(menuId: number): Promise<number> {
  const entry = await db.menuStockTotals.get(menuId);
  return entry ? entry.quantity : -1;
}

export async function decrementIngredientTotal(
  ingredientId: number,
  amount: number,
): Promise<void> {
  await db.ingredientTotals.put({
    ingredientId,
    total: Math.max(0, (await getIngredientTotal(ingredientId)) - amount),
  });
}

export async function decrementMenuStockTotal(
  menuId: number,
  qty: number,
): Promise<void> {
  const current = await getMenuStockTotal(menuId);
  if (current === -1) return;
  await db.menuStockTotals.put({
    menuId,
    quantity: Math.max(0, current - qty),
  });
}
