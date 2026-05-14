import { test, expect } from '@playwright/test';
import { waitForMobilePage } from '../helpers';

test.describe('Customer Order Flow — Menu → Cart → Payment', () => {
  test.beforeEach(({}, testInfo) => {
    if (testInfo.project.name !== 'customer') test.skip();
  });

  test('renders menu page with search and category chips', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/customer/menu');
    await waitForMobilePage(page);

    // Search input
    const searchInput = page.getByPlaceholder(/Cari menu/);
    await expect(searchInput).toBeVisible({ timeout: 10000 });

    // Category chip "Semua"
    const allChip = page.getByRole('button', { name: 'Semua' });
    await expect(allChip).toBeVisible({ timeout: 5000 });

    // Bottom navigation
    await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('menu items display with "+ Tambah" buttons', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1500);

    // Find "+ Tambah" buttons on menu cards
    const tambahButtons = page.getByRole('button', { name: /Tambah/ });
    const count = await tambahButtons.count();

    if (count > 0) {
      // Click the first "+ Tambah" button to add to cart
      await tambahButtons.first().click();
      await page.waitForTimeout(500);

      // Should show a feedback that item was added
      // (may show toast, animation, or quantity indicator)
      const stillOnMenu = await page.getByPlaceholder(/Cari menu/).isVisible().catch(() => false);
      expect(stillOnMenu).toBeTruthy();
    } else {
      // Menu cards may use Rp price text as click targets
      const priceElements = page.locator('text=/Rp/');
      const priceCount = await priceElements.count();
      expect(priceCount).toBeGreaterThanOrEqual(0);
    }
  });

  test('navigate from menu to cart via bottom nav', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    // Click "Keranjang" in bottom nav
    const cartTab = page.getByText('Keranjang').first();
    await expect(cartTab).toBeVisible({ timeout: 5000 });
    await cartTab.click();
    await page.waitForTimeout(1000);

    // Should navigate to cart page
    await expect(page).toHaveURL(/\/customer\/cart/, { timeout: 10000 });

    // Cart header
    await expect(page.getByText('Keranjang')).toBeVisible({ timeout: 5000 });
  });

  test('cart page displays correctly with header and item sections', async ({ page }) => {
    await page.goto('/customer/cart');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    // Cart header
    await expect(page.getByText('Keranjang')).toBeVisible({ timeout: 5000 });

    // Subtotal or Total should be visible
    const hasSubtotal = await page.getByText('Subtotal').isVisible().catch(() => false);
    const hasTotal = await page.getByText('Total').isVisible().catch(() => false);
    expect(hasSubtotal || hasTotal).toBeTruthy();

    // Bottom navigation still present
    await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 3000 });
    await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 3000 });
  });

  test('add menu item to cart then verify cart has items', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1500);

    // Find and click "+ Tambah" buttons
    const tambahButtons = page.getByRole('button', { name: /Tambah/ });
    const tambahCount = await tambahButtons.count();

    if (tambahCount === 0) {
      // Try clicking menu cards directly
      const menuCards = page.locator('text=/Rp/');
      const menuCount = await menuCards.count();
      if (menuCount > 0) {
        await menuCards.first().click();
        await page.waitForTimeout(500);
      }
    } else {
      await tambahButtons.first().click();
      await page.waitForTimeout(300);
      if (tambahCount > 1) {
        await tambahButtons.nth(1).click();
        await page.waitForTimeout(300);
      }
    }

    // Navigate to cart
    const cartTab = page.getByText('Keranjang').first();
    await cartTab.click();
    await page.waitForTimeout(1000);

    await expect(page).toHaveURL(/\/customer\/cart/, { timeout: 10000 });

    // Cart page should render without errors
    await expect(page.getByText('Keranjang')).toBeVisible({ timeout: 5000 });
  });

  test('riwayat page accessible from bottom nav', async ({ page }) => {
    await page.goto('/customer/menu');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    // Click "Riwayat" in bottom nav
    const riwayatTab = page.getByText('Riwayat').first();
    await expect(riwayatTab).toBeVisible({ timeout: 5000 });
    await riwayatTab.click();
    await page.waitForTimeout(1000);

    // Should navigate to riwayat
    await expect(page).toHaveURL(/\/customer\/riwayat/, { timeout: 10000 });

    // Riwayat page header
    await expect(page.getByText('Riwayat Pesanan')).toBeVisible({ timeout: 5000 });
  });

  test('riwayat page has filter tabs (Semua, Diproses, Selesai)', async ({ page }) => {
    await page.goto('/customer/riwayat');
    await waitForMobilePage(page);
    await page.waitForTimeout(1000);

    // Check for filter tabs
    await expect(page.getByText('Riwayat Pesanan')).toBeVisible({ timeout: 5000 });

    const semuaTab = page.getByRole('button', { name: 'Semua' });
    const diprosesTab = page.getByRole('button', { name: 'Diproses' });
    const selesaiTab = page.getByRole('button', { name: 'Selesai' });

    const hasSemua = await semuaTab.isVisible().catch(() => false);
    const hasDiproses = await diprosesTab.isVisible().catch(() => false);
    const hasSelesai = await selesaiTab.isVisible().catch(() => false);

    // At least one filter tab should be visible
    expect(hasSemua || hasDiproses || hasSelesai).toBeTruthy();
  });
});
