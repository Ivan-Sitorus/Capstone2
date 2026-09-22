const MESSAGES = {
    403: { title: 'Akses Ditolak', desc: 'Anda tidak memiliki izin untuk membuka halaman ini.' },
    404: { title: 'Halaman Tidak Ditemukan', desc: 'Maaf, halaman yang Anda cari tidak tersedia atau sudah tidak berlaku.' },
    419: { title: 'Sesi Kedaluwarsa', desc: 'Sesi Anda telah berakhir. Silakan muat ulang dan coba lagi.' },
    500: { title: 'Terjadi Kesalahan', desc: 'Terjadi kesalahan pada server. Silakan coba beberapa saat lagi.' },
    503: { title: 'Layanan Tidak Tersedia', desc: 'Layanan sedang tidak tersedia. Silakan coba beberapa saat lagi.' },
};

export default function ErrorShow({ status = 500 }) {
    const m = MESSAGES[status] ?? { title: 'Terjadi Kesalahan', desc: 'Terjadi kesalahan yang tidak terduga.' };

    return (
        <div style={{
            display: 'flex', flexDirection: 'column', alignItems: 'center',
            justifyContent: 'center', minHeight: '100vh', backgroundColor: '#FAFAFA',
            fontFamily: 'Inter, system-ui, sans-serif', padding: 24, textAlign: 'center',
        }}>
            <div style={{ fontSize: 72, fontWeight: 700, color: '#E8692A', marginBottom: 8 }}>
                {status}
            </div>
            <h1 style={{ fontSize: 22, fontWeight: 600, color: '#1A1A2E', margin: '0 0 8px' }}>
                {m.title}
            </h1>
            <p style={{ fontSize: 14, color: '#6C757D', margin: '0 0 20px', maxWidth: 360 }}>
                {m.desc}
            </p>
            <button
                onClick={() => window.location.reload()}
                style={{
                    fontSize: 14, fontWeight: 600, color: '#FFFFFF', background: '#E8692A',
                    borderRadius: 8, padding: '10px 20px', border: 'none', cursor: 'pointer',
                }}
            >
                Muat Ulang
            </button>
        </div>
    );
}
