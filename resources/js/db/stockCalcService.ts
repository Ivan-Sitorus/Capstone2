import { db } from './dexie-config';
import {
  getIngredientTotal,
  getMenuRecipe,
  getMenuStockTotal,
  decrementIngredientTotal,
  decrementMenuStockTotal,
} from './stockRawStore';

/**
 * Calculate current stock for a single menu.
 * Recipe-based: bottleneck ingredient (floor(total / quantityUsed)).
 * Non-recipe: direct stock from menuStockTotals.
 */
export async function calculateMenuStock(menuId: number): Promise<number> {
  const recipe = await getMenuRecipe(menuId);

  if (recipe.length > 0) {
    let stock = Infinity;
    for (const ri of recipe) {
      const total = await getIngredientTotal(ri.ingredientId);
      if (ri.quantityUsed <= 0) continue;
      const possible = Math.floor(total / ri.quantityUsed);
      stock = Math.min(stock, possible);
    }
    return stock === Infinity ? 0 : stock;
  }

  return getMenuStockTotal(menuId);
}

/**
 * Find all recipe-based menu IDs that use ANY of the given ingredient IDs.
 */
export async function getAffectedMenuIds(
  ingredientIds: number[],
): Promise<number[]> {
  if (ingredientIds.length === 0) return [];
  const idSet = new Set(ingredientIds);
  const recipes = await db.menuRecipes.toArray();
  return recipes
    .filter(r => r.recipe.some(ri => idSet.has(ri.ingredientId)))
    .map(r => r.menuId);
}

/**
 * Process an offline order: decrement stock for each item, then
 * recalculate and persist stock snapshots for all affected menus.
 * Runs in a single atomic Dexie transaction.
 */
export async function processOfflineOrder(
  menuItems: Array<{ menuId: number; qty: number }>,
): Promise<void> {
  if (menuItems.length === 0) return;

  await db.transaction(
    'rw',
    [
      db.ingredientTotals,
      db.menuRecipes,
      db.menuStockTotals,
      db.stockSnapshots,
    ],
    async () => {
      const changedIngredientIds = new Set<number>();
      const nonRecipeMenuIds = new Set<number>();

      // Phase 1: decrement stock for each ordered item
      for (const item of menuItems) {
        const recipe = await db.menuRecipes.get(item.menuId);

        if (recipe && recipe.recipe.length > 0) {
          for (const ri of recipe.recipe) {
            const current = await db.ingredientTotals.get(ri.ingredientId);
            const newTotal = Math.max(
              0,
              (current ? current.total : 0) - item.qty * ri.quantityUsed,
            );
            await db.ingredientTotals.put({
              ingredientId: ri.ingredientId,
              total: newTotal,
            });
            changedIngredientIds.add(ri.ingredientId);
          }
        } else {
          // Non-recipe menu
          const current = await db.menuStockTotals.get(item.menuId);
          if (current) {
            const newQty = Math.max(0, current.quantity - item.qty);
            await db.menuStockTotals.put({
              menuId: item.menuId,
              quantity: newQty,
            });
          }
          nonRecipeMenuIds.add(item.menuId);
        }
      }

      // Phase 2: recalculate stock for ALL affected menus
      const recipeMenuIds = await getAffectedMenuIds(
        [...changedIngredientIds],
      );
      const allAffectedMenuIds = [
        ...new Set([...recipeMenuIds, ...nonRecipeMenuIds]),
      ];

      const now = new Date().toISOString();
      for (const menuId of allAffectedMenuIds) {
        const stock = await calculateMenuStock(menuId);
        await db.stockSnapshots.put({
          menuId,
          stock,
          isUnlimited: stock >= 999999,
          updatedAt: now,
        });
      }
    },
  );
}

/**
 * Recalculate and persist stock for ALL menus (recipe-based and non-recipe).
 */
export async function recalculateAllStocks(): Promise<void> {
  const allRecipeMenus = await db.menuRecipes.toArray();
  const allStockMenus = await db.menuStockTotals.toArray();
  const allMenuIds = [
    ...new Set([
      ...allRecipeMenus.map(r => r.menuId),
      ...allStockMenus.map(s => s.menuId),
    ]),
  ];

  const now = new Date().toISOString();
  for (const menuId of allMenuIds) {
    const stock = await calculateMenuStock(menuId);
    await db.stockSnapshots.put({
      menuId,
      stock,
      isUnlimited: stock >= 999999,
      updatedAt: now,
    });
  }
}
