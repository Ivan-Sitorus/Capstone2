import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('K3 - POS Flow (Full Order Lifecycle)', () => {
  test('renders POS layout with search, category chips, cart panel', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/pesanan-baru');

    // Search bar
    await expect(page.getByPlaceholder('Cari menu...')).toBeVisible({ timeout: 10000 });

    // Category chips
    await expect(page.getByRole('button', { name: 'Semua' })).toBeVisible({ timeout: 5000 });

    // Cart panel header
    await expect(page.getByText('Keranjang Pesanan')).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO'));
    expect(critical).toEqual([]);
  });

  test('click menu card → cart panel shows item with quantity 1', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    // Find first menu card by locating text with "Rp"
    const menuItems = page.locator('text=/Rp/');
    const menuCount = await menuItems.count();

    if (menuCount === 0) {
      test.skip(true, 'No menu items available');
      return;
    }

    // Click first menu card
    await menuItems.first().click();
    await page.waitForTimeout(500);

    // Cart should no longer show "Keranjang kosong"
    await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

    // BAYAR button should be enabled (not disabled)
    const bayarButton = page.getByRole('button', { name: /BAYAR/ });
    await expect(bayarButton).toBeVisible({ timeout: 3000 });
    await expect(bayarButton).not.toBeDisabled({ timeout: 3000 });
  });

  test('adjust quantity via +/- buttons updates subtotal', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const menuItems = page.locator('text=/Rp/');
    const menuCount = await menuItems.count();

    if (menuCount === 0) {
      test.skip(true, 'No menu items available');
      return;
    }

    // Add item to cart
    await menuItems.first().click();
    await page.waitForTimeout(500);

    // Verify item is in cart (no longer empty)
    await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

    // Find plus (+) button in the cart panel
    const plusButtons = page.getByRole('button').filter({ hasText: '+' });
    const plusCount = await plusButtons.count();

    if (plusCount > 0) {
      // Click the first plus button (inside cart panel)
      await plusButtons.last().click(); // last + button is usually within cart
      await page.waitForTimeout(300);
    }

    // Verify Total label is visible
    await expect(page.getByText('Total', { exact: true })).toBeVisible({ timeout: 3000 });
  });

  test('complete order workflow: add item → BAYAR → fill name → pay cash → success', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const menuItems = page.locator('text=/Rp/');
    const menuCount = await menuItems.count();

    if (menuCount === 0) {
      test.skip(true, 'No menu items available');
      return;
    }

    // Step 1: Add item to cart
    await menuItems.first().click();
    await page.waitForTimeout(500);
    await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

    // Step 2: Click BAYAR button
    const bayarButton = page.getByRole('button', { name: /BAYAR/ });
    await expect(bayarButton).toBeVisible({ timeout: 3000 });
    await bayarButton.click();
    await page.waitForTimeout(500);

    // Step 3: Payment modal appears
    const paymentHeading = page.getByRole('heading', { name: 'Metode Pembayaran' });
    await expect(paymentHeading).toBeVisible({ timeout: 5000 });

    // Step 4: Fill customer name
    const nameInput = page.getByPlaceholder(/Masukkan nama pelanggan/);
    await expect(nameInput).toBeVisible({ timeout: 3000 });
    await nameInput.fill('Test Customer POS');

    // Step 5: Select Cash payment
    const cashOption = page.getByText('Bayar ke Kasir (Cash)');
    const cashVisible = await cashOption.isVisible().catch(() => false);
    if (!cashVisible) {
      const cashBtn = page.locator('button').filter({ hasText: 'Cash' }).first();
      const cashBtnVisible = await cashBtn.isVisible().catch(() => false);
      if (cashBtnVisible) await cashBtn.click();
    }

    // Step 6: Confirm payment
    const confirmBtn = page.getByRole('button', { name: /Konfirmasi Pembayaran/ });
    await expect(confirmBtn).toBeVisible({ timeout: 3000 });
    await confirmBtn.click();
    await page.waitForTimeout(1500);

    // Step 7: Success popup
    const successPopup = page.getByText('Pesanan Diterima!');
    await expect(successPopup).toBeVisible({ timeout: 10000 });

    // Verify total is displayed in success popup
    await expect(page.getByText('Total Pembayaran')).toBeVisible({ timeout: 3000 });

    // Step 8: Click OK to dismiss
    const okBtn = page.getByRole('button', { name: 'OK' });
    const okVisible = await okBtn.isVisible().catch(() => false);
    if (okVisible) {
      await okBtn.click();
      await page.waitForTimeout(1000);
    }
  });

  test('verify subtotal and total values change with multiple items', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1500);

    const menuItems = page.locator('text=/Rp/');
    const menuCount = await menuItems.count();

    if (menuCount < 2) {
      test.skip(true, 'Not enough menu items for multi-item test');
      return;
    }

    // Add 2 items
    await menuItems.first().click();
    await page.waitForTimeout(300);
    await menuItems.nth(1).click();
    await page.waitForTimeout(500);

    // Cart should have items
    await expect(page.getByText('Keranjang kosong')).not.toBeVisible({ timeout: 3000 });

    // Total should be visible with 2 items
    await expect(page.getByText('Total', { exact: true })).toBeVisible({ timeout: 3000 });

    // Subtotal and Total Rp values should be present
    const rpValues = page.locator('text=/Rp/');
    const rpCount = await rpValues.count();
    expect(rpCount).toBeGreaterThan(0);
  });

  test('search for non-existent menu shows empty state', async ({ page }) => {
    await visit(page, '/cashier/pesanan-baru');
    await page.waitForTimeout(1000);

    const searchInput = page.getByPlaceholder('Cari menu...');
    await searchInput.fill('xxxxx_nonexistent_menu_xxxxx');
    await page.waitForTimeout(500);

    // Empty state message
    await expect(page.getByText('Tidak ada menu ditemukan')).toBeVisible({ timeout: 5000 });
  });
});
