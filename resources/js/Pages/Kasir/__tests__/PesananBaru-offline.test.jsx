import '@testing-library/jest-dom';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';

// ════════════════════════════════════════════════════════════════
// Hoisted mutable state shared across mocks
// ════════════════════════════════════════════════════════════════
const { mockRouterPost, mockSaveOrder, mockClearCart, mockCartItems, mockIsOnlineRef } = vi.hoisted(() => ({
    mockRouterPost: vi.fn(),
    mockSaveOrder: vi.fn().mockResolvedValue(1),
    mockClearCart: vi.fn(),
    mockCartItems: [],
    mockIsOnlineRef: { current: true },
}));

// ════════════════════════════════════════════════════════════════
// Mock all heavy dependencies
// ════════════════════════════════════════════════════════════════

vi.mock('@inertiajs/react', () => ({
    router: { post: mockRouterPost, reload: vi.fn(), visit: vi.fn() },
    Head: () => null,
    Link: ({ children }) => children,
}));

vi.mock('@/Hooks/useNetworkStatus', () => ({
    useNetworkStatus: () => ({ isOnline: mockIsOnlineRef.current }),
}));

vi.mock('@/db/offlineOrderStore', () => ({
    saveOrder: (...args) => mockSaveOrder(...args),
}));

vi.mock('@/Store/cartStore', () => ({
    default: vi.fn((selector) => {
        const state = {
            cashierItems: mockCartItems,
            cashierAddItem: vi.fn(),
            cashierRemoveItem: vi.fn(),
            cashierIncrement: vi.fn(),
            cashierDecrement: vi.fn(),
            cashierClearCart: mockClearCart,
        };
        return typeof selector === 'function' ? selector(state) : state;
    }),
}));

vi.mock('@/Layouts/CashierLayout', () => ({
    default: ({ children }) => <div data-testid="cashier-layout">{children}</div>,
}));

vi.mock('@/Components/Shared/SharedMenuItem', () => ({
    default: () => <div>menu-item</div>,
}));

vi.mock('@/Components/Shared/SharedCartItem', () => ({
    default: ({ item, onRemove }) => (
        <div data-testid="cart-item">{item.name} x{item.quantity}</div>
    ),
}));

vi.mock('@/Components/Shared/Modal', () => ({
    default: ({ isOpen, children }) => (isOpen ? <div data-testid="modal">{children}</div> : null),
}));

vi.mock('@/Components/Shared/FlashToast', () => ({
    default: () => null,
}));

vi.mock('@/Components/Cashier/WhatsAppShareModal', () => ({
    default: () => null,
}));

vi.mock('@/components/ui/button', () => ({
    Button: ({ children, onClick, disabled, className, variant, size }) => (
        <button
            onClick={onClick}
            disabled={disabled}
            className={className}
            data-variant={variant}
            data-size={size}
        >
            {children}
        </button>
    ),
}));

vi.mock('@/components/ui/input', () => ({
    Input: (props) => <input {...props} />,
}));

vi.mock('@/helpers', () => ({
    formatRupiah: (v) => `Rp ${Number(v).toLocaleString('id-ID')}`,
    formatDate: (d) => d,
    formatTime: (d) => d,
}));

vi.mock('@/lib/utils', () => ({
    cn: (...args) => args.filter(Boolean).join(' '),
}));

vi.mock('qrcode.react', () => ({
    QRCodeCanvas: () => null,
}));

vi.mock('lucide-react', () => ({
    Search: () => null,
    X: () => null,
    Banknote: () => null,
    Lock: () => null,
    User: () => null,
    CircleCheck: () => null,
    Clock: () => null,
    Printer: () => null,
    Percent: () => null,
    MessageSquare: () => null,
}));

// ════════════════════════════════════════════════════════════════
// Global mocks (route helper — available as $route() in Laravel / Inertia)
// ════════════════════════════════════════════════════════════════
globalThis.route = vi.fn((name) => `/cashier/${name.replace(/\./g, '/')}`);

// ════════════════════════════════════════════════════════════════
// Import AFTER mocks (vitest handles hoisting)
// ════════════════════════════════════════════════════════════════
import PesananBaru from '../PesananBaru';

// ════════════════════════════════════════════════════════════════
// Helpers
// ════════════════════════════════════════════════════════════════

const sampleCategories = [
    {
        id: 1,
        name: 'Kopi',
        menus: [
            { id: 1, name: 'Kopi Robusta', price: 12000, stock: 10 },
            { id: 2, name: 'Kopi Latte', price: 15000, stock: 10 },
        ],
    },
];

function renderPage() {
    return render(<PesananBaru categories={sampleCategories} promotions={[]} />);
}

async function openPayModalAndFillName(name = 'Budi') {
    const bayarBtn = screen.getByRole('button', { name: /bayar/i });
    fireEvent.click(bayarBtn);

    const nameInput = screen.getByPlaceholderText('Masukkan nama pelanggan...');
    fireEvent.change(nameInput, { target: { value: name } });

    const confirmBtn = screen.getByRole('button', { name: /konfirmasi pembayaran/i });
    fireEvent.click(confirmBtn);
}

// ════════════════════════════════════════════════════════════════
// Tests
// ════════════════════════════════════════════════════════════════

describe('PesananBaru offline interception', () => {
    beforeEach(() => {
        vi.clearAllMocks();

        mockCartItems.length = 0;
        mockIsOnlineRef.current = true;

        mockCartItems.push(
            { menuId: 1, name: 'Kopi Robusta', price: 12000, cashback: 0, quantity: 2, image: null },
        );
    });

    it('calls saveOrder and NOT router.post when offline', async () => {
        mockIsOnlineRef.current = false;

        renderPage();
        await openPayModalAndFillName();

        await waitFor(() => {
            expect(mockSaveOrder).toHaveBeenCalledTimes(1);
        });

        expect(mockRouterPost).not.toHaveBeenCalled();

        // Verify saveOrder was called with proper payload
        const payload = mockSaveOrder.mock.calls[0][0];
        expect(payload).toHaveProperty('uuid');
        expect(payload).toHaveProperty('items');
        expect(payload).toHaveProperty('paymentMethod', 'cash');
        expect(payload).toHaveProperty('customerName', 'Budi');
        expect(payload).toHaveProperty('isMahasiswa', false);
        expect(payload).toHaveProperty('total');
        expect(payload).toHaveProperty('createdAt');

        // Verify items shape
        expect(payload.items).toHaveLength(1);
        expect(payload.items[0]).toEqual({
            menuId: 1,
            name: 'Kopi Robusta',
            qty: 2,
            price: 12000,
            subtotal: 24000,
        });
    });

    it('calls router.post and NOT saveOrder when online', async () => {
        mockIsOnlineRef.current = true;

        renderPage();
        await openPayModalAndFillName();

        await waitFor(() => {
            expect(mockRouterPost).toHaveBeenCalledTimes(1);
        });

        expect(mockSaveOrder).not.toHaveBeenCalled();

        // Verify router.post received correct route and data
        expect(mockRouterPost).toHaveBeenCalledWith(
            expect.stringContaining('pesanan-baru'),
            expect.objectContaining({
                customer_name: 'Budi',
            }),
            expect.any(Object),
        );
    });

    it('clears cart after successful offline save', async () => {
        mockIsOnlineRef.current = false;

        renderPage();
        await openPayModalAndFillName();

        await waitFor(() => {
            expect(mockSaveOrder).toHaveBeenCalledTimes(1);
        });

        expect(mockClearCart).toHaveBeenCalled();
    });

    it('closes payment modal after offline save', async () => {
        mockIsOnlineRef.current = false;

        renderPage();
        await openPayModalAndFillName();

        await waitFor(() => {
            expect(screen.queryByPlaceholderText('Masukkan nama pelanggan...')).not.toBeInTheDocument();
        });
    });

    it('shows offline toast after successful offline save', async () => {
        mockIsOnlineRef.current = false;

        renderPage();
        await openPayModalAndFillName();

        await waitFor(() => {
            expect(screen.getByText('Pesanan disimpan offline ✓')).toBeInTheDocument();
        });
    });
});
