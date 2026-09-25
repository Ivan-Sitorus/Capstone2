import { BRAND_ORANGE, WARM_BORDER, WHITE, WARM_TEXT_MUTED } from '@/theme';

export default function CategoryChip({ label, active, onClick }) {
    return (
        <button
            onClick={onClick}
            style={{
                height: 38,
                padding: '0 18px',
                borderRadius: 16,
                border: `1px solid ${active ? BRAND_ORANGE : WARM_BORDER}`,
                background: active ? BRAND_ORANGE : WHITE,
                color: active ? WHITE : WARM_TEXT_MUTED,
                fontSize: 12,
                fontWeight: active ? 700 : 600,
                cursor: 'pointer',
                whiteSpace: 'nowrap',
                flexShrink: 0,
                boxShadow: active ? '0 3px 10px rgba(232,118,58,0.25)' : 'none',
                transition: 'all 0.15s',
            }}
        >
            {label}
        </button>
    );
}
