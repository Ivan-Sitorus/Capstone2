import { formatRupiah } from '@/helpers';
import { WHITE, GRAY_100, GRAY_200, GRAY_400, GRAY_500, GRAY_900, BLUE } from '@/theme';

export default function CartItem({ item, onIncrement, onDecrement }) {
    return (
        <div style={{ display: 'flex', alignItems: 'center', padding: '14px 0', borderBottom: `1px solid ${GRAY_100}`, gap: 12 }}>
            {/* Name + unit price */}
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 14, fontWeight: 600, color: GRAY_900, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {item.name}
                </div>
                <div style={{ fontSize: 12, color: GRAY_400, marginTop: 2 }}>
                    {formatRupiah(item.price)}
                </div>
            </div>

            {/* [-] qty [+] */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexShrink: 0 }}>
                <button
                    onClick={() => onDecrement(item.menuId)}
                    style={{ width: 30, height: 30, background: WHITE, color: GRAY_500, border: `1px solid ${GRAY_200}`, borderRadius: 6, fontSize: 18, fontWeight: 700, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                >
                    −
                </button>

                <span style={{ fontSize: 14, fontWeight: 600, color: GRAY_900, minWidth: 20, textAlign: 'center' }}>
                    {item.quantity}
                </span>

                <button
                    onClick={() => onIncrement(item.menuId)}
                    style={{ width: 30, height: 30, background: BLUE, color: WHITE, border: 'none', borderRadius: 6, fontSize: 18, fontWeight: 700, cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                >
                    +
                </button>

                {/* Item total */}
                <span style={{ fontSize: 14, fontWeight: 700, color: GRAY_900, minWidth: 76, textAlign: 'right', flexShrink: 0 }}>
                    {formatRupiah(item.price * item.quantity)}
                </span>
            </div>
        </div>
    );
}
