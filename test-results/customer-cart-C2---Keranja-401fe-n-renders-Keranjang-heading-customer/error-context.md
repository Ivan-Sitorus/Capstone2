# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: customer/cart.spec.ts >> C2 - Keranjang Pelanggan >> renders Keranjang heading
- Location: tests/playwright/customer/cart.spec.ts:9:3

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
  - generic [ref=e6]: Keranjang
  - generic [ref=e7]:
    - img [ref=e9]
    - generic [ref=e12]:
      - paragraph [ref=e13]: Keranjang Kosong
      - paragraph [ref=e14]: Tambahkan menu favoritmu dari halaman menu
    - button "Kembali ke Menu" [ref=e15] [cursor=pointer]
  - navigation [ref=e17]:
    - link "Menu" [ref=e18] [cursor=pointer]:
      - /url: /customer/menu
      - img [ref=e20]
      - generic [ref=e22]: Menu
    - link "Keranjang" [ref=e23] [cursor=pointer]:
      - /url: /customer/cart
      - img [ref=e25]
      - generic [ref=e29]: Keranjang
    - link "Riwayat" [ref=e30] [cursor=pointer]:
      - /url: /customer/riwayat
      - img [ref=e32]
      - generic [ref=e35]: Riwayat
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | import { waitForMobilePage } from '../helpers';
  3  | 
  4  | test.describe('C2 - Keranjang Pelanggan', () => {
  5  |   test.beforeEach(({ page }, testInfo) => {
  6  |     if (testInfo.project.name !== 'customer') test.skip();
  7  |   });
  8  | 
  9  |   test('renders Keranjang heading', async ({ page }) => {
  10 |     const errors: string[] = [];
  11 |     page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
  12 | 
  13 |     await page.goto('/customer/cart');
  14 |     await waitForMobilePage(page);
  15 | 
  16 |     await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 10000 });
  17 | 
  18 |     const critical = errors.filter(e => !e.includes('favicon'));
> 19 |     expect(critical).toEqual([]);
     |                      ^ Error: expect(received).toEqual(expected) // deep equality
  20 |   });
  21 | 
  22 |   test('displays empty state Keranjang Kosong with Kembali ke Menu button', async ({ page }) => {
  23 |     await page.goto('/customer/cart');
  24 |     await waitForMobilePage(page);
  25 | 
  26 |     const isEmpty = await page.getByText('Keranjang Kosong').isVisible().catch(() => false);
  27 |     const hasItems = await page.getByText('RINGKASAN PESANAN').isVisible().catch(() => false);
  28 | 
  29 |     expect(isEmpty || hasItems).toBeTruthy();
  30 | 
  31 |     if (isEmpty) {
  32 |       const backBtn = page.getByRole('button', { name: /Kembali ke Menu/ });
  33 |       await expect(backBtn).toBeVisible({ timeout: 3000 });
  34 |     }
  35 |   });
  36 | 
  37 |   test('displays bottom navigation with 3 tabs', async ({ page }) => {
  38 |     await page.goto('/customer/cart');
  39 |     await waitForMobilePage(page);
  40 | 
  41 |     await expect(page.getByText('Menu').first()).toBeVisible({ timeout: 5000 });
  42 |     await expect(page.getByText('Keranjang').first()).toBeVisible({ timeout: 5000 });
  43 |     await expect(page.getByText('Riwayat').first()).toBeVisible({ timeout: 5000 });
  44 |   });
  45 | });
  46 | 
```