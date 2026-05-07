import { test, expect } from '@playwright/test';
import { waitForInertia, visit } from '../helpers';

test.describe('K6 - Detail Pesanan', () => {
  test.describe.configure({ mode: 'serial' });

  test('navigates from riwayat to detail via Detail link', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const detailLink = page.getByText('Detail').first();
    const hasDetailLink = await detailLink.isVisible().catch(() => false);

    if (!hasDetailLink) { test.skip(true, 'No Detail link available'); return; }

    await detailLink.click();
    await waitForInertia(page);

    await expect(page.getByText(/Detail Pesanan/)).toBeVisible({ timeout: 10000 });

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('heading contains order_code with #ORD- prefix', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const detailLink = page.getByText('Detail').first();
    if (!(await detailLink.isVisible().catch(() => false))) { test.skip(true, 'No Detail link'); return; }

    await detailLink.click();
    await waitForInertia(page);

    const heading = page.getByRole('heading', { name: /Detail Pesanan/ });
    await expect(heading).toBeVisible({ timeout: 10000 });
    const text = await heading.textContent();
    expect(text).toMatch(/#ORD-/);
  });

  test('displays back arrow button to pesanan-aktif', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const detailLink = page.getByText('Detail').first();
    if (!(await detailLink.isVisible().catch(() => false))) { test.skip(true, 'No Detail link'); return; }

    await detailLink.click();
    await waitForInertia(page);

    const backBtn = page.locator('a[href="/cashier/pesanan-aktif"]');
    await expect(backBtn).toBeVisible({ timeout: 5000 });
  });

  test('Daftar Item Pesanan section has 4 columns', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const detailLink = page.getByText('Detail').first();
    if (!(await detailLink.isVisible().catch(() => false))) { test.skip(true, 'No Detail link'); return; }

    await detailLink.click();
    await waitForInertia(page);

    await expect(page.getByText('Daftar Item Pesanan')).toBeVisible({ timeout: 5000 });

    for (const header of ['Nama Item', 'Harga', 'Jumlah', 'Subtotal']) {
      await expect(page.getByText(header)).toBeVisible();
    }

    await expect(page.getByText('Total Pembayaran')).toBeVisible({ timeout: 3000 });
  });

  test('Informasi Pesanan card shows key fields', async ({ page }) => {
    await visit(page, '/cashier/riwayat');
    await page.waitForTimeout(1000);

    const detailLink = page.getByText('Detail').first();
    if (!(await detailLink.isVisible().catch(() => false))) { test.skip(true, 'No Detail link'); return; }

    await detailLink.click();
    await waitForInertia(page);

    await expect(page.getByText('Informasi Pesanan')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('ID Pesanan')).toBeVisible();
    await expect(page.getByText('Metode Pembayaran')).toBeVisible();
    await expect(page.getByText('Kasir')).toBeVisible();
    await expect(page.getByText('Status')).toBeVisible();
  });
});
