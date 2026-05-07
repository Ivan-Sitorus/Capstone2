# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: customer/riwayat.spec.ts >> C3 - Riwayat Pesanan Pelanggan >> renders TOTAL ORDER stats bar
- Location: tests/playwright/customer/riwayat.spec.ts:9:3

# Error details

```
Error: expect(received).toEqual(expected) // deep equality

- Expected  - 1
+ Received  + 3

- Array []
+ Array [
+   "Loading the stylesheet 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap' violates the following Content Security Policy directive: \"style-src 'self' 'unsafe-inline' http://127.0.0.1:5173 http://localhost:5173\". Note that 'style-src-elem' was not explicitly set, so 'style-src' is used as a fallback. The action has been blocked.",
+ ]
```

# Page snapshot

```yaml
- generic [ref=e3]:
  - generic [ref=e4]:
    - generic [ref=e6]:
      - generic [ref=e7]: TOTAL ORDER
      - generic [ref=e8]: "0"
    - generic [ref=e9]:
      - button "Semua" [ref=e10] [cursor=pointer]
      - button "Pending" [ref=e11] [cursor=pointer]
      - button "Diproses" [ref=e12] [cursor=pointer]
      - button "Selesai" [ref=e13] [cursor=pointer]
    - generic [ref=e15]:
      - img [ref=e17]
      - generic [ref=e20]:
        - generic [ref=e21]: Belum ada pesanan
        - generic [ref=e22]: Pesanan kamu akan muncul di sini
  - navigation [ref=e24]:
    - link "Menu" [ref=e25] [cursor=pointer]:
      - /url: /customer/menu
      - img [ref=e27]
      - generic [ref=e29]: Menu
    - link "Keranjang" [ref=e30] [cursor=pointer]:
      - /url: /customer/cart
      - img [ref=e32]
      - generic [ref=e36]: Keranjang
    - link "Riwayat" [ref=e37] [cursor=pointer]:
      - /url: /customer/riwayat
      - img [ref=e39]
      - generic [ref=e42]: Riwayat
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { waitForMobilePage } from '../helpers';
  3  | 
  4  | test.describe('C3 - Riwayat Pesanan Pelanggan', () => {
  5  |   test.beforeEach(({ page }, testInfo) => {
  6  |     if (testInfo.project.name !== 'customer') test.skip();
  7  |   });
  8  | 
  9  |   test('renders TOTAL ORDER stats bar', async ({ page }) => {
  10 |     const errors: string[] = [];
  11 |     page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  12 | 
  13 |     await page.goto('/customer/riwayat');
  14 |     await waitForMobilePage(page);
  15 | 
  16 |     await expect(page.getByText('TOTAL ORDER')).toBeVisible({ timeout: 10000 });
  17 | 
  18 |     const critical = errors.filter(e => !e.includes('favicon'));
> 19 |     expect(critical).toEqual([]);
     |                      ^ Error: expect(received).toEqual(expected) // deep equality
  20 |   });
  21 | 
  22 |   test('displays filter tabs: Semua, Pending, Diproses, Selesai', async ({ page }) => {
  23 |     await page.goto('/customer/riwayat');
  24 |     await waitForMobilePage(page);
  25 | 
  26 |     await expect(page.getByRole('button', { name: 'Semua' })).toBeVisible({ timeout: 5000 });
  27 |     await expect(page.getByRole('button', { name: 'Pending' })).toBeVisible({ timeout: 5000 });
  28 |     await expect(page.getByRole('button', { name: 'Diproses' })).toBeVisible({ timeout: 5000 });
  29 |     await expect(page.getByRole('button', { name: 'Selesai' })).toBeVisible({ timeout: 5000 });
  30 |   });
  31 | 
  32 |   test('shows empty state or order cards', async ({ page }) => {
  33 |     await page.goto('/customer/riwayat');
  34 |     await waitForMobilePage(page);
  35 | 
  36 |     const isEmpty = await page.getByText(/Belum ada pesanan/).isVisible().catch(() => false);
  37 |     const hasOrders = await page.locator('text=/#ORD-/').first().isVisible().catch(() => false);
  38 | 
  39 |     expect(isEmpty || hasOrders).toBeTruthy();
  40 |   });
  41 | 
  42 |   test('clicking a filter tab changes active state', async ({ page }) => {
  43 |     await page.goto('/customer/riwayat');
  44 |     await waitForMobilePage(page);
  45 | 
  46 |     const selesaiBtn = page.getByRole('button', { name: 'Selesai' });
  47 |     await selesaiBtn.click();
  48 |     await page.waitForTimeout(500);
  49 |     await expect(selesaiBtn).toBeVisible();
  50 |   });
  51 | 
  52 |   test('displays bottom navigation with 3 tabs', async ({ page }) => {
  53 |     await page.goto('/customer/riwayat');
  54 |     await waitForMobilePage(page);
  55 | 
  56 |     await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 5000 });
  57 |     await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
  58 |     await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  59 |   });
  60 | });
  61 | 
```