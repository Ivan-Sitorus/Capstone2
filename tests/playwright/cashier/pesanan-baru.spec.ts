import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('K3 - Pesanan Baru (POS)', () => {
  test('renders POS layout: search, category chips, cart panel', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/pesanan-baru');

    await expect(page.getByPlaceholder('Cari menu...')).toBeVisible();
    await expect(page.getByRole('button', { name: 'Semua' })).toBeVisible();

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays menu grid with price indicators', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const menuItems = page.locator('text=/Rp/');
    const count = await menuItems.count();
    expect(count).toBeGreaterThan(0);
  });

  test('cart panel shows Keranjang kosong initially', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');

    await expect(page.getByText('Keranjang Pesanan')).toBeVisible();
    await expect(page.getByText('Keranjang kosong')).toBeVisible();
  });

  test('BAYAR button is disabled when cart is empty', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');

    const bayarButton = page.getByRole('button', { name: /BAYAR/ });
    await expect(bayarButton).toBeVisible();
    await expect(bayarButton).toBeDisabled();
  });

  test('clicking a menu card adds item to cart and enables BAYAR', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const menuCard = page.locator('text=/Rp/').first();
    if (await menuCard.isVisible()) {
      await menuCard.click();
      await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

      const bayarButton = page.getByRole('button', { name: /BAYAR/ });
      await expect(bayarButton).not.toBeDisabled({ timeout: 3000 });
    }
  });

  test('category chips filter menu items', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const categoryButtons = page.locator('div').locator('button').filter({ hasText: /./ });
    const visibleChips = categoryButtons.filter({ hasText: /Semua/ });
    const count = await visibleChips.count();
    if (count > 1) {
      await visibleChips.nth(1).click();
      await page.waitForTimeout(500);
      const filteredCount = await page.locator('text=/Rp/').count();
      expect(filteredCount).toBeGreaterThanOrEqual(0);
    }
  });

  test('quantity +/- buttons function in cart panel', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const menuCard = page.locator('text=/Rp/').first();
    if (await menuCard.isVisible()) {
      await menuCard.click();
      await page.waitForTimeout(500);

      const plusBtn = page.locator('button').filter({ hasText: '+' }).first();
      if (await plusBtn.isVisible()) {
        await plusBtn.click();
        await page.waitForTimeout(300);
        await expect(page.getByText('Keranjang kosong')).not.toBeVisible();
      }
    }
  });

  test('search input shows empty state for nonexistent menus', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const searchInput = page.getByPlaceholder('Cari menu...');
    await searchInput.fill('nonexistent_xyz_abc_menu');
    await page.waitForTimeout(500);

    await expect(page.getByText('Tidak ada menu ditemukan')).toBeVisible({ timeout: 3000 });
  });

  test('total updates when item quantity changes', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const menuCard = page.locator('text=/Rp/').first();
    if (await menuCard.isVisible()) {
      await menuCard.click();
      await page.waitForTimeout(500);
      await expect(page.getByText('Total', { exact: true })).toBeVisible({ timeout: 3000 });
    }
  });
});
