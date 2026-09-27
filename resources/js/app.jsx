import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import { route } from 'ziggy-js';
import { ORANGE } from '@/theme';
import CashierLayout from '@/Layouts/CashierLayout';
import '../css/app.css';

// Make route() available globally (used in components)
window.route = (name, params, absolute) => route(name, params, absolute);

const pages = import.meta.glob('./Pages/**/*.jsx');

// Layout persisten per area; layout milik halaman sendiri tidak ditimpa.
// Area Customer sengaja dilewatkan karena halamannya masih inline CustomerLayout.
const areaLayouts = {
    Cashier: page => <CashierLayout fullscreen>{page}</CashierLayout>,
};

// Inertia v3 hanya membaca initial page dari
// <script data-page="app" type="application/json">.
// Adapter server yang terpasang (inertia-laravel v2) masih memakai
// <div id="app" data-page="...">, jadi format lama dibaca sebagai fallback
// agar hidrasi muat-pertama tetap jalan sampai adapter server di-upgrade ke v3.
// Begitu server mengirim elemen <script>, fallback ini otomatis tidak terpakai.
const initialPage = (() => {
    if (typeof document === 'undefined') return undefined;

    const scriptEl = document.querySelector(
        'script[data-page="app"][type="application/json"]',
    );
    if (scriptEl?.textContent) return undefined;

    const el = document.getElementById('app');
    if (!el?.dataset?.page) return undefined;

    try {
        return JSON.parse(el.dataset.page);
    } catch {
        return undefined;
    }
})();

createInertiaApp({
    page: initialPage,
    async resolve(name) {
        const module = await pages[`./Pages/${name}.jsx`]();
        const page = module.default;

        if (page.layout === undefined) {
            const layout = areaLayouts[name.split('/')[0]];
            if (layout) page.layout = layout;
        }

        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: { color: ORANGE },
});
