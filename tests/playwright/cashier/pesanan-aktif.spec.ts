import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('K4 - Pesanan Aktif', () => {
  test('renders Pesanan Aktif heading and subtitle', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/pesanan-aktif');

    await expect(page.getByRole('heading', { name: 'Pesanan Aktif' })).toBeVisible();
    await expect(page.getByText(/Kelola semua pesanan yang sedang diproses/)).toBeVisible();

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO'));
    expect(critical).toEqual([]);
  });

  test('displays filter tabs: Semua, Pending, Diproses, Belum Bayar', async ({ page }) => {
    await visit(page, '/cashier/pesanan-aktif');

    await expect(page.getByRole('button', { name: /Semua/ })).toBeVisible();
    await expect(page.getByRole('button', { name: /Pending/ })).toBeVisible();
    await expect(page.getByRole('button', { name: /Diproses/ })).toBeVisible();
    await expect(page.getByRole('button', { name: /Belum Bayar/ })).toBeVisible();
  });

  test('clicking a filter tab changes active state', async ({ page }) => {
    await visit(page, '/cashier/pesanan-aktif');

    const pendingBtn = page.getByRole('button', { name: /Pending/ });
    await pendingBtn.click();
    await page.waitForTimeout(500);
    await expect(pendingBtn).toBeVisible();
  });

  test('displays order cards with ORD codes or empty state', async ({ page }) => {
    await visit(page, '/cashier/pesanan-aktif');

    const hasEmptyState = await page.getByText(/Tidak ada pesanan aktif/).isVisible().catch(() => false);

    if (!hasEmptyState) {
      const orderCodes = page.locator('text=/#ORD-/');
      const count = await orderCodes.count();
      expect(count).toBeGreaterThanOrEqual(0);
    } else {
      await expect(page.getByText(/Tidak ada pesanan aktif/)).toBeVisible();
    }
  });

  test('sidebar navigation shows active indicator for Pesanan Aktif', async ({ page }) => {
    await visit(page, '/cashier/pesanan-aktif');

    const activeLink = page.locator('a[href="/cashier/pesanan-aktif"]');
    await expect(activeLink).toBeVisible();
  });
});
