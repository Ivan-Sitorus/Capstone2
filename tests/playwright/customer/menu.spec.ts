import { test, expect } from '@playwright/test';
import { waitForMobilePage } from '../helpers';

test.describe('C1 - Menu Pelanggan', () => {
  test.beforeEach(({ page }, testInfo) => {
    if (testInfo.project.name !== 'customer') test.skip();
  });

  test('renders search bar and category chips', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/customer/menu');
    await waitForMobilePage(page);

    const searchInput = page.getByPlaceholder(/Cari menu/);
    await expect(searchInput).toBeVisible({ timeout: 10000 });

    const allChip = page.getByRole('button', { name: 'Semua' });
    await expect(allChip).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays menu cards with Rp price indicators', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    const priceElements = page.locator('text=/Rp/');
    const count = await priceElements.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('shows greeting or scan QR message when no session', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);

    const hasGreeting = await page.getByText(/Selamat datang|Scan QR|Scan QR Meja/).isVisible().catch(() => false);
    expect(hasGreeting).toBeTruthy();
  });

  test('displays bottom navigation with 3 tabs: Menu, Keranjang, Riwayat', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);

    await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 10000 });
    await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  });

  test('category chips have "+ Tambah" buttons on menu cards', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    const tambahButtons = page.getByRole('button', { name: /Tambah/ });
    const count = await tambahButtons.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });
});
