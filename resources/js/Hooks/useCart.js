import useCartStore from '@/Store/cartStore';

export default function useCart() {
    const items     = useCartStore(s => s.items);
    const tableId   = useCartStore(s => s.tableId);
    const setTable  = useCartStore(s => s.setTable);
    const addItem   = useCartStore(s => s.addItem);
    const removeItem= useCartStore(s => s.removeItem);
    const updateQty = useCartStore(s => s.updateQty);
    const clearCart = useCartStore(s => s.clearCart);
    const total     = useCartStore(s => s.total());
    const count     = useCartStore(s => s.count());

    return {
        items, tableId, setTable, addItem, removeItem, updateQty, clearCart,
        total, count,
        totalQty: count, /* backward-compat alias used in Menu/Index */
    };
}
