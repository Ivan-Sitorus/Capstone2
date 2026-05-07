import { test, expect } from '@playwright/test';
import { waitForInertia, visit } from '../helpers';

test.describe('Integration - Full Order Flow', () => {
  test('POS → add item → pay cash → verify in pesanan-aktif', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const menuItems = page.locator('text=/Rp/');
    const menuCount = await menuItems.count();

    if (menuCount === 0) {
      test.skip(true, 'No menu items available for testing order flow');
      return;
    }

    await menuItems.first().click();
    await page.waitForTimeout(500);

    await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

    const bayarButton = page.getByRole('button', { name: /BAYAR/ });
    await expect(bayarButton).toBeVisible({ timeout: 3000 });
    await bayarButton.click();
    await page.waitForTimeout(500);

    const modal = page.getByRole('heading', { name: 'Metode Pembayaran' });
    await expect(modal).toBeVisible({ timeout: 5000 });

    const nameInput = page.getByPlaceholder(/Masukkan nama pelanggan/);
    await expect(nameInput).toBeVisible({ timeout: 3000 });
    await nameInput.fill('Test Customer');

    const cashOption = page.getByText('Bayar ke Kasir (Cash)');
    const cashVisible = await cashOption.isVisible().catch(() => false);
    if (!cashVisible) {
      const cashBtn = page.locator('button').filter({ hasText: 'Cash' }).first();
      await cashBtn.click();
    }

    const confirmBtn = page.getByRole('button', { name: /Konfirmasi Pembayaran/ });
    await expect(confirmBtn).toBeVisible({ timeout: 3000 });
    await confirmBtn.click();

    const successPopup = page.getByText('Pesanan Diterima!');
    await expect(successPopup).toBeVisible({ timeout: 10000 });

    await expect(page.getByText('Total Pembayaran')).toBeVisible({ timeout: 3000 });

    const okBtn = page.getByRole('button', { name: 'OK' });
    await expect(okBtn).toBeVisible({ timeout: 3000 });
    await okBtn.click();

    await page.waitForURL(/\/cashier\/pesanan-aktif/, { timeout: 10000 });
    await waitForInertia(page);

    await expect(page.getByRole('heading', { name: 'Pesanan Aktif' })).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO'));
    expect(critical).toEqual([]);
  });

  test('empty cart has disabled BAYAR button', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const bayarButton = page.getByRole('button', { name: /BAYAR/ });
    await expect(bayarButton).toBeVisible();
    await expect(bayarButton).toBeDisabled();
  });

  test('add multiple items and verify cart has items', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const menuItems = page.locator('text=/Rp/');
    const count = await menuItems.count();

    if (count < 2) {
      test.skip(true, 'Not enough menu items for multi-item test');
      return;
    }

    await menuItems.first().click();
    await page.waitForTimeout(300);
    await menuItems.nth(1).click();
    await page.waitForTimeout(300);

    await expect(page.getByText('Keranjang Pesanan')).toBeVisible();
    await expect(page.getByText('Total', { exact: true })).toBeVisible({ timeout: 3000 });
  });
});
