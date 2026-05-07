# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: customer/menu.spec.ts >> C1 - Menu Pelanggan >> renders search bar and category chips
- Location: tests/playwright/customer/menu.spec.ts:9:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: getByPlaceholder(/Cari menu/)
Expected: visible
Timeout: 10000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 10000ms
  - waiting for getByPlaceholder(/Cari menu/)

```

# Page snapshot

```yaml
- generic [ref=e4]:
  - img "W9 Cafe" [ref=e6]
  - heading "Scan QR Meja" [level=1] [ref=e7]
  - paragraph [ref=e8]: Silakan scan QR code yang ada di meja Anda untuk mulai memesan.
  - paragraph [ref=e9]: Hubungi kasir jika membutuhkan bantuan.
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { waitForMobilePage } from '../helpers';
  3  | 
  4  | test.describe('C1 - Menu Pelanggan', () => {
  5  |   test.beforeEach(({ page }, testInfo) => {
  6  |     if (testInfo.project.name !== 'customer') test.skip();
  7  |   });
  8  | 
  9  |   test('renders search bar and category chips', async ({ page }) => {
  10 |     const errors: string[] = [];
  11 |     page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  12 | 
  13 |     await page.goto('/customer/menu');
  14 |     await waitForMobilePage(page);
  15 | 
  16 |     const searchInput = page.getByPlaceholder(/Cari menu/);
> 17 |     await expect(searchInput).toBeVisible({ timeout: 10000 });
     |                               ^ Error: expect(locator).toBeVisible() failed
  18 | 
  19 |     const allChip = page.getByRole('button', { name: 'Semua' });
  20 |     await expect(allChip).toBeVisible({ timeout: 5000 });
  21 | 
  22 |     const critical = errors.filter(e => !e.includes('favicon'));
  23 |     expect(critical).toEqual([]);
  24 |   });
  25 | 
  26 |   test('displays menu cards with Rp price indicators', async ({ page }) => {
  27 |     await page.goto('/customer/menu');
  28 |     await waitForMobilePage(page);
  29 |     await page.waitForTimeout(1000);
  30 | 
  31 |     const priceElements = page.locator('text=/Rp/');
  32 |     const count = await priceElements.count();
  33 |     expect(count).toBeGreaterThanOrEqual(0);
  34 |   });
  35 | 
  36 |   test('shows greeting or scan QR message when no session', async ({ page }) => {
  37 |     await page.goto('/customer/menu');
  38 |     await waitForMobilePage(page);
  39 | 
  40 |     const hasGreeting = await page.getByText(/Selamat datang|Scan QR|Scan QR Meja/).isVisible().catch(() => false);
  41 |     expect(hasGreeting).toBeTruthy();
  42 |   });
  43 | 
  44 |   test('displays bottom navigation with 3 tabs: Menu, Keranjang, Riwayat', async ({ page }) => {
  45 |     await page.goto('/customer/menu');
  46 |     await waitForMobilePage(page);
  47 | 
  48 |     await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 10000 });
  49 |     await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
  50 |     await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  51 |   });
  52 | 
  53 |   test('category chips have "+ Tambah" buttons on menu cards', async ({ page }) => {
  54 |     await page.goto('/customer/menu');
  55 |     await waitForMobilePage(page);
  56 |     await page.waitForTimeout(1000);
  57 | 
  58 |     const tambahButtons = page.getByRole('button', { name: /Tambah/ });
  59 |     const count = await tambahButtons.count();
  60 |     expect(count).toBeGreaterThanOrEqual(0);
  61 |   });
  62 | });
  63 | 
```