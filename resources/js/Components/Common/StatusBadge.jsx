import { GOLD, GOLD_BG, BLUE_MUTED, BLUE_MUTED_BG, GREEN_MUTED, GREEN_MUTED_BG, RED_MUTED, RED_MUTED_BG, RED, RED_50, NEUTRAL_100, NEUTRAL_400, NEUTRAL_500 } from '@/theme';

// Pencil design tokens — brown/coffee theme
const statusMap = {
    // Order statuses
    pending:    { dot: GOLD, text: GOLD, bg: GOLD_BG, label: 'Pending'  },
    processing: { dot: BLUE_MUTED, text: BLUE_MUTED, bg: BLUE_MUTED_BG, label: 'Diproses' },
    completed:  { dot: GREEN_MUTED, text: GREEN_MUTED, bg: GREEN_MUTED_BG, label: 'Selesai'  },
    cancelled:  { dot: RED_MUTED, text: RED_MUTED, bg: RED_MUTED_BG, label: 'Dibatalkan' },
    unpaid:     { dot: RED, text: RED, bg: RED_50, label: 'Belum Lunas' },
    // Verifikasi statuses
    menunggu:  { dot: GOLD, text: GOLD, bg: GOLD_BG, label: 'Menunggu'  },
    disetujui: { dot: GREEN_MUTED, text: GREEN_MUTED, bg: GREEN_MUTED_BG, label: 'Disetujui' },
    ditolak:   { dot: RED_MUTED, text: RED_MUTED, bg: RED_MUTED_BG, label: 'Ditolak'   },
};

export default function StatusBadge({ status }) {
    const s = statusMap[status] ?? { dot: NEUTRAL_400, text: NEUTRAL_500, bg: NEUTRAL_100, label: status };
    return (
        <span style={{
            display: 'inline-flex', alignItems: 'center', gap: 6,
            background: s.bg,
            borderRadius: 100,
            padding: '0 10px',
            height: 28,
            fontSize: 12, fontWeight: 600,
            color: s.text,
            fontFamily: 'Outfit, system-ui',
            whiteSpace: 'nowrap',
        }}>
            <span style={{
                width: 6, height: 6, borderRadius: '50%',
                background: s.dot, flexShrink: 0,
                display: 'inline-block',
            }} />
            {s.label}
        </span>
    );
}
