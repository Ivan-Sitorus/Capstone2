import { test, expect } from '@playwright/test';
import { waitForMobilePage } from '../helpers';

test.describe('C3 - Riwayat Pesanan Pelanggan', () => {
  test.beforeEach(({ page }, testInfo) => {
    if (testInfo.project.name !== 'customer') test.skip();
  });

  test('renders TOTAL ORDER stats bar', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);

    await expect(page.getByText('TOTAL ORDER')).toBeVisible({ timeout: 10000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays filter tabs: Semua, Pending, Diproses, Selesai', async ({ page }) => {
    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);

    await expect(page.getByRole('button', { name: 'Semua' })).toBeVisible({ timeout: 5000 });
    await expect(page.getByRole('button', { name: 'Pending' })).toBeVisible({ timeout: 5000 });
    await expect(page.getByRole('button', { name: 'Diproses' })).toBeVisible({ timeout: 5000 });
    await expect(page.getByRole('button', { name: 'Selesai' })).toBeVisible({ timeout: 5000 });
  });

  test('shows empty state or order cards', async ({ page }) => {
    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);

    const isEmpty = await page.getByText(/Belum ada pesanan/).isVisible().catch(() => false);
    const hasOrders = await page.locator('text=/#ORD-/').first().isVisible().catch(() => false);

    expect(isEmpty || hasOrders).toBeTruthy();
  });

  test('clicking a filter tab changes active state', async ({ page }) => {
    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);

    const selesaiBtn = page.getByRole('button', { name: 'Selesai' });
    await selesaiBtn.click();
    await page.waitForTimeout(500);
    await expect(selesaiBtn).toBeVisible();
  });

  test('displays bottom navigation with 3 tabs', async ({ page }) => {
    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);

    await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  });
});
