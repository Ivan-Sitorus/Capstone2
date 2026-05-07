import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('K5 - Riwayat Pesanan', () => {
  test('renders Riwayat Pesanan heading and subtitle', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/riwayat');

    await expect(page.getByRole('heading', { name: 'Riwayat Pesanan' })).toBeVisible();
    await expect(page.getByText(/Lihat semua transaksi yang telah selesai/)).toBeVisible();

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays filter bar with search, date input, and payment method select', async ({ page }) => {
    await visit(page, '/cashier/riwayat');

    await expect(page.getByPlaceholder('Cari transaksi...')).toBeVisible();
    await expect(page.locator('input[type="date"]')).toBeVisible();
    await expect(page.locator('select')).toBeVisible();
  });

  test('table displays 8 columns with headers', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1500);

    const columnLabels = ['ID Pesanan', 'Tanggal', 'Waktu', 'Total', 'Pembayaran', 'Kasir', 'Status', 'Aksi'];
    for (const label of columnLabels) {
      await expect(page.getByText(label, { exact: true })).toBeVisible({ timeout: 3000 });
    }
  });

  test('shows order data or empty state message', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const hasOrderCodes = await page.locator('text=/#ORD-/').first().isVisible().catch(() => false);
    const hasNoData = await page.getByText(/Tidak ada data riwayat pesanan/).isVisible().catch(() => false);
    const hasPagination = await page.getByText(/Halaman/).isVisible().catch(() => false);

    expect(hasOrderCodes || hasNoData || hasPagination).toBeTruthy();
  });

  test('search filters table by order code', async ({ page }) => {
    await visit(page, '/cashier/riwayat');

    const searchInput = page.getByPlaceholder('Cari transaksi...');
    await searchInput.fill('zzz_nonexistent_order_99999');
    await page.waitForTimeout(800);

    const noData = page.getByText(/Tidak ada data riwayat pesanan/);
    await expect(noData).toBeVisible({ timeout: 5000 });
  });

  test('payment method dropdown contains Semua Metode, Tunai, QRIS', async ({ page }) => {
    await visit(page, '/cashier/riwayat');

    const methodSelect = page.locator('select');
    await expect(methodSelect).toContainText('Semua Metode');
    await expect(methodSelect).toContainText('Tunai');
    await expect(methodSelect).toContainText('QRIS');
  });
});
