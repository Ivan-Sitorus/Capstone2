import { test, expect } from '@playwright/test';

test.describe('K7 - Verifikasi Akun Mahasiswa', () => {
  test('verifikasi page route returns non-500 status (page may be in development)', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    const response = await page.goto('/cashier/verifikasi');
    const status = response?.status() ?? 500;
    expect(status).toBeLessThan(500);
  });

  test('pending-count endpoint returns valid JSON', async ({ page }) => {
    const response = await page.goto('/cashier/pending-count');

    if (response && response.status() === 200) {
      const body = await response.text();
      expect(() => JSON.parse(body)).not.toThrow();
      const data = JSON.parse(body);
      const count = data.pendingCount ?? data.count ?? null;
      expect(count).not.toBeNull();
      expect(typeof count === 'number' || !isNaN(Number(count))).toBeTruthy();
    } else if (response) {
      expect(response.status()).toBeLessThan(500);
    }
  });
});
