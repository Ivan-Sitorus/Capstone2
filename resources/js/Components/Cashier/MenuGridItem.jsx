import { formatRupiah } from '@/helpers';
import { WHITE, GRAY_300, GRAY_500, GRAY_900, BLUE } from '@/theme';

export default function MenuGridItem({ menu, onAdd }) {
    return (
        <div
            onClick={() => onAdd(menu)}
            style={{
                background: WHITE,
                border: `1.5px solid ${GRAY_300}`,
                borderRadius: 8,
                padding: '20px 16px',
                cursor: 'pointer',
                boxShadow: 'none',
                transition: 'box-shadow 0.15s, transform 0.1s',
                display: 'flex',
                flexDirection: 'column',
                gap: 6,
                textAlign: 'center',
                fontFamily: "'Inter', system-ui, sans-serif",
            }}
            onMouseEnter={e => {
                e.currentTarget.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
                e.currentTarget.style.transform = 'translateY(-1px)';
            }}
            onMouseLeave={e => {
                e.currentTarget.style.boxShadow = 'none';
                e.currentTarget.style.transform = 'translateY(0)';
            }}
        >
            <div style={{ fontSize: 11, textTransform: 'uppercase', color: GRAY_500, letterSpacing: '0.6px', fontWeight: 600 }}>
                {menu.category?.name}
            </div>
            <div style={{ fontSize: 15, fontWeight: 700, color: GRAY_900, lineHeight: 1.3 }}>
                {menu.name}
            </div>
            <div style={{ fontSize: 14, fontWeight: 700, color: BLUE, fontFamily: "'DM Sans', 'Inter', system-ui" }}>
                {formatRupiah(menu.price)}
            </div>
        </div>
    );
}
