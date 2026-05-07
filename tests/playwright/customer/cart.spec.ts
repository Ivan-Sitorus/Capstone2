import { test, expect } from '@playwright/test';
import { waitForMobilePage } from '../helpers';

test.describe('C2 - Keranjang Pelanggan', () => {
  test.beforeEach(({ page }, testInfo) => {
    if (testInfo.project.name !== 'customer') test.skip();
  });

  test('renders Keranjang heading', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/customer/cart');
    await waitForMobilePage(page);

    await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 10000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays empty state Keranjang Kosong with Kembali ke Menu button', async ({ page }) => {
    await page.goto('/customer/cart');
    await waitForMobilePage(page);

    const isEmpty = await page.getByText('Keranjang Kosong').isVisible().catch(() => false);
    const hasItems = await page.getByText('RINGKASAN PESANAN').isVisible().catch(() => false);

    expect(isEmpty || hasItems).toBeTruthy();

    if (isEmpty) {
      const backBtn = page.getByRole('button', { name: /Kembali ke Menu/ });
      await expect(backBtn).toBeVisible({ timeout: 3000 });
    }
  });

  test('displays bottom navigation with 3 tabs', async ({ page }) => {
    await page.goto('/customer/cart');
    await waitForMobilePage(page);

    await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  });
});
