import { test, expect } from '@playwright/test';
import { waitForInertia, visit } from '../helpers';

test.describe('K2 - Dashboard Kasir', () => {
  test('renders Dashboard heading, subtitle, and date chip', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/dashboard');

    await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    await expect(page.getByText(/Selamat datang, Kasir!/)).toBeVisible();

    const critical = errors.filter(e =>
      !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO')
    );
    expect(critical).toEqual([]);
  });

  test('displays stat bar with 3 columns', async ({ page }) => {
    await visit(page, '/cashier/dashboard');

    await expect(page.getByText('Total Penjualan')).toBeVisible();
    await expect(page.getByText('Jumlah Transaksi')).toBeVisible();
    await expect(page.locator('text=Pesanan Aktif').first()).toBeVisible();
    await expect(page.getByText('Rp')).toBeVisible();
  });

  test('displays quick action buttons', async ({ page }) => {
    await visit(page, '/cashier/dashboard');

    await expect(page.getByRole('button', { name: /Pesanan Baru/ })).toBeVisible();
    await expect(page.getByRole('button', { name: /Lihat Pesanan/ })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Riwayat' })).toBeVisible();
  });

  test('displays Transaksi Terbaru section with table', async ({ page }) => {
    await visit(page, '/cashier/dashboard');

    await expect(page.getByText('Transaksi Terbaru')).toBeVisible();
    await expect(page.getByText(/Lihat Semua/)).toBeVisible();

    const headers = page.locator('th');
    const headerCount = await headers.count();
    expect(headerCount).toBeGreaterThanOrEqual(3);
  });

  test('sidebar navigation renders all links', async ({ page }) => {
    await visit(page, '/cashier/dashboard');

    await expect(page.getByText('W9 Cafe')).toBeVisible();
    await expect(page.getByRole('link', { name: /Dashboard/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /Pesanan Baru/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /Pesanan Aktif/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /Riwayat Pesanan/ })).toBeVisible();
    await expect(page.getByRole('link', { name: /Profil/ })).toBeVisible();
    await expect(page.getByRole('button', { name: /Keluar/ })).toBeVisible();
  });
});
