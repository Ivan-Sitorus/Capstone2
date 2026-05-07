import { test, expect } from '@playwright/test';
import { visit } from '../helpers';

test.describe('K8 - Profil Kasir', () => {
  test('renders Profil Saya heading and subtitle', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });

    await visit(page, '/cashier/profil');

    await expect(page.getByRole('heading', { name: 'Profil Saya' })).toBeVisible();
    await expect(page.getByText(/Kelola informasi akun Anda/)).toBeVisible();

    const critical = errors.filter(e => !e.includes('favicon'));
    expect(critical).toEqual([]);
  });

  test('displays avatar card with name and role badge "Kasir"', async ({ page }) => {
    await visit(page, '/cashier/profil');

    await expect(page.getByText('Nama Lengkap')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Email')).toBeVisible({ timeout: 5000 });

    const roleBadge = page.getByText('Kasir');
    await expect(roleBadge.first()).toBeVisible({ timeout: 3000 });
  });

  test('displays 4 read-only information fields', async ({ page }) => {
    await visit(page, '/cashier/profil');

    await expect(page.getByText('Nama Lengkap')).toBeVisible({ timeout: 5000 });
    await expect(page.getByText('Email')).toBeVisible();
    await expect(page.getByText('Peran / Role')).toBeVisible();
    await expect(page.getByText('Terdaftar Sejak')).toBeVisible();
  });

  test('displays Keluar dari Akun red logout button', async ({ page }) => {
    await visit(page, '/cashier/profil');

    const logoutBtn = page.getByRole('button', { name: /Keluar dari Akun/ });
    await expect(logoutBtn).toBeVisible();
  });

  test('sidebar navigation shows active indicator for Profil', async ({ page }) => {
    await visit(page, '/cashier/profil');

    const activeLink = page.locator('a[href="/cashier/profil"]');
    await expect(activeLink).toBeVisible();
  });
});
