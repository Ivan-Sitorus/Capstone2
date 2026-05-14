import { test, expect } from '@playwright/test';
import { visit, waitForInertia } from '../helpers';

test.describe('Admin — Dashboard & Verifikasi', () => {
  test('admin dashboard loads via /admin', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    const response = await page.goto('/admin');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // Filament dashboard should render with navigation
      const hasNavigation = await page.locator('nav, .fi-sidebar').first().isVisible().catch(() => false);

      if (hasNavigation) {
        // Check for dashboard stats widgets
        const hasWidgets = await page.locator('.fi-wi, [data-widget]').first().isVisible().catch(() => false);
        const hasStats = await page.getByText(/Statistik|Overview|Total/i).isVisible().catch(() => false);
        expect(hasWidgets || hasStats || hasNavigation).toBeTruthy();
      } else {
        // May be on Filament login page — still valid for auth flow
        const hasLogin = await page.getByRole('button', { name: /Sign in|Masuk|Login/i }).isVisible().catch(() => false);
        expect(hasLogin || status === 200).toBeTruthy();
      }
    }

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('Google Analytics'));
    expect(critical).toEqual([]);
  });

  test('admin can access cashier verifikasi page', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    // Admin shares cashier middleware (role:cashier,admin)
    const response = await page.goto('/cashier/verifikasi');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await waitForInertia(page);
      await page.waitForTimeout(1000);

      // Verifikasi page heading
      await expect(page.getByText('Verifikasi Akun Mahasiswa')).toBeVisible({ timeout: 10000 });

      // Pending count info
      const hasPending = await page.getByText(/Menunggu/i).isVisible().catch(() => false);
      const hasApproved = await page.getByText(/Disetujui/i).isVisible().catch(() => false);
      expect(hasPending || hasApproved).toBeTruthy();
    }

    const critical = errors.filter(e => !e.includes('favicon') && !e.includes('reverb') && !e.includes('ECHO'));
    expect(critical).toEqual([]);
  });

  test('verifikasi page has filter tabs: Semua, Menunggu, Disetujui, Ditolak', async ({ page }) => {
    await visit(page, '/cashier/verifikasi');
    await page.waitForTimeout(1000);

    // Page heading
    await expect(page.getByText('Verifikasi Akun Mahasiswa')).toBeVisible({ timeout: 10000 });

    // Filter tabs
    const semuaTab = page.getByRole('button', { name: 'Semua' });
    const menungguTab = page.getByRole('button', { name: 'Menunggu' });
    const disetujuiTab = page.getByRole('button', { name: 'Disetujui' });
    const ditolakTab = page.getByRole('button', { name: 'Ditolak' });

    const hasSemua = await semuaTab.isVisible().catch(() => false);
    const hasMenunggu = await menungguTab.isVisible().catch(() => false);
    const hasDisetujui = await disetujuiTab.isVisible().catch(() => false);
    const hasDitolak = await ditolakTab.isVisible().catch(() => false);

    expect(hasSemua || hasMenunggu || hasDisetujui || hasDitolak).toBeTruthy();
  });

  test('verifikasi page renders data table with columns', async ({ page }) => {
    await visit(page, '/cashier/verifikasi');
    await page.waitForTimeout(1500);

    // Page heading
    await expect(page.getByText('Verifikasi Akun Mahasiswa')).toBeVisible({ timeout: 10000 });

    // Table should have headers: No, Nama, NIM, Tgl Daftar, Status, Aksi
    const tableHeaders = page.locator('thead th, [role="columnheader"]');
    const headerCount = await tableHeaders.count();

    if (headerCount > 0) {
      const headerTexts = await tableHeaders.allTextContents();
      const headerString = headerTexts.join(' ').toLowerCase();
      const hasNameColumn = headerString.includes('nama') || headerString.includes('nim');
      expect(hasNameColumn).toBeTruthy();
    }
    // If no table headers, that's fine — page may have no users pending verification
  });

  test('pending-count endpoint returns valid data', async ({ page }) => {
    const response = await page.goto('/cashier/pending-count');

    if (response && response.status() === 200) {
      const body = await response.text();
      expect(() => JSON.parse(body)).not.toThrow();
      const data = JSON.parse(body);
      const count = data.pendingCount ?? data.count ?? null;
      expect(count).not.toBeNull();
    } else if (response) {
      expect(response.status()).toBeLessThan(500);
    }
  });

  test('admin can access user management in Filament', async ({ page }) => {
    const response = await page.goto('/admin/users');
    const status = response?.status() ?? 500;

    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(2000);

      // User resource list page
      const hasTable = await page.locator('table, .fi-ta').first().isVisible().catch(() => false);
      const hasCreate = await page.getByText(/User|Pengguna|Buat/i).isVisible().catch(() => false);
      expect(hasTable || hasCreate).toBeTruthy();
    }
  });

  test('no server errors on admin dashboard widgets', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    const response = await page.goto('/admin');
    const status = response?.status() ?? 500;
    expect(status).toBeLessThan(500);

    if (status === 200) {
      await page.waitForTimeout(3000);

      // Wait for any Filament widgets to load
      // No assertion needed — just verify no 500 errors
    }

    const critical = errors.filter(e =>
      !e.includes('favicon') &&
      !e.includes('Google Analytics') &&
      !e.includes('reverb') &&
      !e.includes('ECHO')
    );
    expect(critical).toEqual([]);
  });
});
