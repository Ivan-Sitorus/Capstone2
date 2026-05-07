import { test, expect } from '@playwright/test';
import { waitForMobilePage } from '../helpers';

test.describe('C4 - Identitas Pelanggan (Customer Login)', () => {
  test.beforeEach(({ page }, testInfo) => {
    if (testInfo.project.name !== 'customer') test.skip();
  });

  test('shows Scan QR Meja page when no table parameter', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await page.goto('/order');
    await waitForMobilePage(page);

    await expect(page.getByText('Scan QR Meja')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText(/scan QR code yang ada di meja/i)).toBeVisible({ timeout: 5000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('renders W9 Cafe logo and Selamat Datang with table param', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    await expect(page.getByText('Selamat Datang!')).toBeVisible({ timeout: 10000 });
    await expect(page.getByText(/Meja No./)).toBeVisible({ timeout: 5000 });
  });

  test('displays Nama and Nomor Telepon input fields', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    await expect(page.getByText('Nama')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Nomor Telepon')).toBeVisible({ timeout: 5000 });

    const nameInput = page.locator('input[type="text"]');
    await expect(nameInput).toBeVisible();

    const phoneInput = page.locator('input[type="tel"]');
    await expect(phoneInput).toBeVisible();
  });

  test('displays mahasiswa checkbox option', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    await expect(page.getByText(/mahasiswa STIE Totalwin Semarang/)).toBeVisible({ timeout: 5000 });
  });

  test('displays Masuk submit button', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    const masukBtn = page.getByRole('button', { name: 'Masuk' });
    await expect(masukBtn).toBeVisible({ timeout: 5000 });
  });

  test('validates empty form shows error messages', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    const masukBtn = page.getByRole('button', { name: 'Masuk' });
    await masukBtn.click();
    await page.waitForTimeout(500);

    const nameError = page.getByText(/Nama minimal 2 karakter/);
    const phoneError = page.getByText(/Nomor telepon tidak valid/);
    const hasError = await nameError.isVisible().catch(() => false) || await phoneError.isVisible().catch(() => false);
    expect(hasError).toBeTruthy();
  });

  test('successful identity entry redirects to menu', async ({ page }) => {
    await page.goto('/order?table=1');
    await waitForMobilePage(page);

    await page.fill('input[type="text"]', 'Test Customer');
    await page.fill('input[type="tel"]', '081234567890');

    const masukBtn = page.getByRole('button', { name: 'Masuk' });
    await masukBtn.click();

    await page.waitForURL(/\/customer\/menu/, { timeout: 10000 });
    await expect(page).toHaveURL(/\/customer\/menu/);
  });
});
