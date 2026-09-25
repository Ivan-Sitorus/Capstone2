import { formatRupiah } from '@/helpers';
import { WARM_BG, WARM_TEXT, WARM_TEXT_MUTED, BRAND_ORANGE, WHITE } from '@/theme';

export default function CartItem({ item, onIncrement, onDecrement, showDivider = true }) {
    return (
        <div style={{
            display: 'flex', alignItems: 'center', gap: 14,
            padding: '16px 18px',
            borderBottom: showDivider ? `1px solid ${WARM_BG}` : 'none',
        }}>
            {/* Item info */}
            <div style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 2 }}>
                <span style={{
                    fontSize: 15, fontWeight: 600, color: WARM_TEXT,
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    {item.name}
                </span>
                <span style={{
                    fontSize: 13, color: WARM_TEXT_MUTED,
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    {formatRupiah(item.price)}
                </span>
            </div>

            {/* Qty controls */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                {/* Minus */}
                <button
                    onClick={() => onDecrement(item.menuId)}
                    style={{
                        width: 32, height: 32, borderRadius: 12,
                        background: WARM_BG, border: 'none',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        fontSize: 18, fontWeight: 600, color: WARM_TEXT,
                        cursor: 'pointer',
                    }}
                >
                    −
                </button>

                {/* Quantity */}
                <span style={{
                    fontSize: 16, fontWeight: 700, color: WARM_TEXT,
                    minWidth: 24, textAlign: 'center',
                    fontFamily: 'Outfit, system-ui, sans-serif',
                }}>
                    {item.quantity}
                </span>

                {/* Plus */}
                <button
                    onClick={() => onIncrement(item.menuId)}
                    style={{
                        width: 32, height: 32, borderRadius: 12,
                        background: BRAND_ORANGE, border: 'none',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        fontSize: 18, fontWeight: 600, color: WHITE,
                        cursor: 'pointer',
                        boxShadow: '0 2px 6px rgba(232,118,58,0.25)',
                    }}
                >
                    +
                </button>
            </div>
        </div>
    );
}
