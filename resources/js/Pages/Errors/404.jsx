export default function NotFound({ status, message }) {
    return (
        <div style={{
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            justifyContent: 'center',
            minHeight: '100vh',
            backgroundColor: '#FAFAFA',
            fontFamily: 'Inter, system-ui, sans-serif',
            padding: '24px',
            textAlign: 'center',
        }}>
            <div style={{ fontSize: '72px', fontWeight: 700, color: '#E8692A', marginBottom: '8px' }}>
                404
            </div>
            <h1 style={{ fontSize: '22px', fontWeight: 600, color: '#1A1A2E', margin: '0 0 8px' }}>
                Halaman Tidak Ditemukan
            </h1>
            <p style={{ fontSize: '14px', color: '#6C757D', margin: '0 0 24px', maxWidth: '360px' }}>
                {message || 'Maaf, halaman yang Anda cari tidak tersedia atau sudah tidak berlaku.'}
            </p>
            <a
                href="/pelanggan/menu"
                style={{
                    display: 'inline-block',
                    backgroundColor: '#E8692A',
                    color: '#FFFFFF',
                    padding: '12px 32px',
                    borderRadius: '50px',
                    fontSize: '14px',
                    fontWeight: 600,
                    textDecoration: 'none',
                }}
            >
                Kembali ke Menu
            </a>
        </div>
    );
}
