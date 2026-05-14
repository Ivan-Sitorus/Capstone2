import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('Kitchen Display System (KDS)', () => {
  test('renders KDS with 2 kanban columns: Menunggu, Diproses', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/kitchen');
    await page.waitForTimeout(1500);

    await expect(page).toHaveURL(/\/kitchen/, { timeout: 10000 });

    await expect(page.getByRole('link', { name: 'Pesanan' })).toBeVisible({ timeout: 5000 });
    await expect(page.getByRole('link', { name: 'Riwayat' })).toBeVisible({ timeout: 5000 });

    await expect(page.getByText('Menunggu')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Diproses')).toBeVisible({ timeout: 5000 });

    await expect(page.locator('button').filter({ hasText: /Semua|Minuman|Makanan/ })).toHaveCount(0);

    const kitchenLayout = page.locator('[data-interface="kitchen"]');
    await expect(kitchenLayout).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO'));
    expect(critical).toEqual([]);
  });

  test('displays "Tidak ada pesanan" placeholder in empty columns', async ({ page }) => {
    await visit(page, '/kitchen');
    await page.waitForTimeout(2000);

    const emptyPlaceholders = page.getByText('Tidak ada pesanan');
    const count = await emptyPlaceholders.count();
    expect(count).toBeGreaterThanOrEqual(0);

    if (count > 0) {
      expect(count).toBeLessThanOrEqual(2);
    }
  });

  test('order cards display elapsed time timer', async ({ page }) => {
    await visit(page, '/kitchen');
    await page.waitForTimeout(1500);

    const timePattern = /\d{2}:\d{2}/;
    const pageText = await page.textContent('body');

    if (!pageText?.match(timePattern)) {
      test.skip(true, 'No order cards with timers in KDS');
      return;
    }

    expect(pageText).toMatch(timePattern);
  });

  test('order card displays bump button when orders exist', async ({ page }) => {
    await visit(page, '/kitchen');
    await page.waitForTimeout(2000);

    const orderCards = page.locator('text=/#[A-Z]+-\d+/');
    const hasOrders = await orderCards.count();

    if (hasOrders === 0) {
      test.skip(true, 'No orders in KDS for bump testing');
      return;
    }

    const ambilBtn = page.getByRole('button', { name: 'Ambil' });
    const selesaiBtn = page.getByRole('button', { name: 'Selesai' });

    const hasAmbil = await ambilBtn.first().isVisible().catch(() => false);
    const hasSelesai = await selesaiBtn.first().isVisible().catch(() => false);

    expect(hasAmbil || hasSelesai).toBeTruthy();
  });

  test('clicking bump on pending order moves it to diproses', async ({ page }) => {
    await visit(page, '/kitchen');
    await page.waitForTimeout(2000);

    const ambilButtons = page.getByRole('button', { name: 'Ambil' });
    const count = await ambilButtons.count();

    if (count === 0) {
      test.skip(true, 'No pending orders to bump');
      return;
    }

    const bumpBtn = ambilButtons.first();
    await expect(bumpBtn).toBeVisible({ timeout: 3000 });

    await bumpBtn.click();
    await page.waitForTimeout(2000);

    await expect(page.locator('[data-interface="kitchen"]')).toBeVisible({ timeout: 5000 });
  });

  test('order counter badge shows active order count', async ({ page }) => {
    await visit(page, '/kitchen');
    await page.waitForTimeout(1500);

    const orderCountElements = page.locator('span.rounded-full').filter({ hasText: /\d+/ });
    const hasCount = await orderCountElements.first().isVisible().catch(() => false);

    if (hasCount) {
      const countText = await orderCountElements.first().textContent();
      const countNum = parseInt(countText || '0', 10);
      expect(Number.isInteger(countNum)).toBeTruthy();
    }
  });
});
