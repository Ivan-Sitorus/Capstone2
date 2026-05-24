import { useEffect, useRef } from 'react';
import useCartStore from '@/Store/cartStore';
import { db } from '@/db/dexie-config';

const CART_ID = 1;

// ── Persist cashier cart to Dexie IndexedDB ─────────────────────────

const saveToDexie = async (items: ReturnType<typeof useCartStore.getState>['cashierItems']) => {
  try {
    await db.cashierCart.put({ items, updatedAt: new Date().toISOString() }, CART_ID);
  } catch { /* quota exceeded or DB not available — silently degrade */ }
};

const loadFromDexie = async () => {
  try {
    return await db.cashierCart.get(CART_ID);
  } catch {
    return undefined;
  }
};

// ── Hook ────────────────────────────────────────────────────────────

export function useCashierCart() {
  const cashierItems = useCartStore(s => s.cashierItems);
  const hydrateCashierCart = useCartStore(s => s.hydrateCashierCart);
  const cashierTotal = useCartStore(s => s.cashierTotal());
  const cashierCount = useCartStore(s => s.cashierCount());

  const cashierAddItem = useCartStore(s => s.cashierAddItem);
  const cashierRemoveItem = useCartStore(s => s.cashierRemoveItem);
  const cashierUpdateQty = useCartStore(s => s.cashierUpdateQty);
  const cashierClearCart = useCartStore(s => s.cashierClearCart);
  const cashierIncrement = useCartStore(s => s.cashierIncrement);
  const cashierDecrement = useCartStore(s => s.cashierDecrement);

  // ── Hydrate from IndexedDB on mount ──────────────────────────────

  useEffect(() => {
    if (typeof window === 'undefined') return;
    loadFromDexie().then(entry => {
      if (entry?.items?.length && useCartStore.getState().cashierItems.length === 0) {
        hydrateCashierCart(entry.items);
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // ── Write-through: persist to Dexie on every change (debounced 500ms) ─

  const debounceRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      saveToDexie(cashierItems);
    }, 500);
    return () => {
      if (debounceRef.current) clearTimeout(debounceRef.current);
    };
  }, [cashierItems]);

  return {
    cashierItems,
    cashierTotal,
    cashierCount,
    cashierAddItem,
    cashierRemoveItem,
    cashierUpdateQty,
    cashierClearCart,
    cashierIncrement,
    cashierDecrement,
  };
}

export default useCashierCart;
