# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: thesis/offline-sync.spec.ts >> Metrik 4: Batch Size Optimization >> 4. Batch size 10
- Location: tests/playwright/thesis/offline-sync.spec.ts:319:5

# Error details

```
SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
```

# Page snapshot

```yaml
- generic [ref=e5]:
  - generic [ref=e6]:
    - img [ref=e8]
    - generic [ref=e11]: W9 Cafe POS
    - generic [ref=e12]: Masuk ke sistem Point of Sale
  - generic [ref=e13]:
    - generic [ref=e14]:
      - generic [ref=e15]: Email
      - textbox "kasir@w9cafe.com" [ref=e16]
    - generic [ref=e17]:
      - generic [ref=e18]: Kata Sandi
      - textbox "Masukkan kata sandi..." [ref=e19]
    - button "Masuk" [ref=e20]
```

# Test source

```ts
  240 |     await setOnlineStatus(page, false);
  241 |     await clearDexie(page);
  242 |     await injectOfflineOrders(page, 20);
  243 | 
  244 |     const startTime = Date.now();
  245 |     await setOnlineStatus(page, true);
  246 | 
  247 |     let synced = false;
  248 |     for (let i = 0; i < 30; i++) {
  249 |       await page.waitForTimeout(1000);
  250 |       const pending = await countDexieOrders(page, 'pending_sync');
  251 |       if (pending === 0) { synced = true; break; }
  252 |     }
  253 |     const recoveryTime = Date.now() - startTime;
  254 |     expect(synced).toBe(true);
  255 | 
  256 |     test.info().annotations.push({ type: 'Recovery Time (20 orders)', description: `${recoveryTime} ms` });
  257 |     console.log(`Recovery Time for 20 orders: ${recoveryTime} ms (${(recoveryTime/20).toFixed(1)} ms/order)`);
  258 |   });
  259 | });
  260 | 
  261 | // ════════════════════════════════════════════════════════════
  262 | // METRIK 3 — Maximum Order Capacity
  263 | // ════════════════════════════════════════════════════════════
  264 | test.describe('Metrik 3: Maximum Order Capacity', () => {
  265 |   test('3a. Storage estimate per order', async ({ page }) => {
  266 |     await page.goto(`${BASE}/kasir/pesanan-baru`, { waitUntil: 'domcontentloaded' });
  267 |     await page.waitForSelector('#app[data-page]', { timeout: 15000 });
  268 | 
  269 |     const before = await getStorageEstimate(page);
  270 | 
  271 |     // Inject 50 orders
  272 |     await injectOfflineOrders(page, 50);
  273 | 
  274 |     const after = await getStorageEstimate(page);
  275 |     const deltaBytes = (after.usage - before.usage);
  276 |     const bytesPerOrder = deltaBytes / 50;
  277 | 
  278 |     console.log(`Storage before: ${(before.usage / 1024).toFixed(2)} KB`);
  279 |     console.log(`Storage after 50 orders: ${(after.usage / 1024).toFixed(2)} KB`);
  280 |     console.log(`Delta: ${(deltaBytes / 1024).toFixed(2)} KB`);
  281 |     console.log(`Bytes per order: ${bytesPerOrder.toFixed(0)} bytes (~${(bytesPerOrder / 1024).toFixed(2)} KB)`);
  282 |     console.log(`Max orders (est.): ${Math.floor(after.quota / bytesPerOrder).toLocaleString()}`);
  283 | 
  284 |     expect(bytesPerOrder).toBeGreaterThan(0);
  285 |     expect(bytesPerOrder).toBeLessThan(50000); // sanity check: < 50KB per order
  286 | 
  287 |     await clearDexie(page);
  288 |   });
  289 | 
  290 |   test('3b. Max queue enforcement at 50 orders', async ({ page }) => {
  291 |     // Inject 50 orders — should succeed
  292 |     await page.goto(`${BASE}/kasir/pesanan-baru`, { waitUntil: 'domcontentloaded' });
  293 |     await page.waitForSelector('#app[data-page]', { timeout: 15000 });
  294 |     await clearDexie(page);
  295 |     await injectOfflineOrders(page, 50);
  296 |     const count = await countDexieOrders(page);
  297 |     expect(count).toBe(50);
  298 | 
  299 |     // Inject 1 more — should be blocked by client-side code
  300 |     // The max 50 limit is enforced by offlineOrderStore.saveOrder()
  301 |     // For this we verify directly
  302 |     await clearDexie(page);
  303 |   });
  304 | });
  305 | 
  306 | // ════════════════════════════════════════════════════════════
  307 | // METRIK 4 — Batch Size Optimization (via API)
  308 | // ════════════════════════════════════════════════════════════
  309 | test.describe('Metrik 4: Batch Size Optimization', () => {
  310 |   // Establish auth session so page.request.post() has cookies
  311 |   test.beforeEach(async ({ page }) => {
  312 |     await page.goto(`${BASE}/kasir/pesanan-baru`, { waitUntil: 'domcontentloaded' });
  313 |     await page.waitForSelector('#app[data-page]', { timeout: 15000 });
  314 |   });
  315 | 
  316 |   const BATCH_SIZES = [10, 25, 50];
  317 | 
  318 |   for (const size of BATCH_SIZES) {
  319 |     test(`4. Batch size ${size}`, async ({ page }) => {
  320 |       // Generate test orders
  321 |       const orders = [];
  322 |       for (let i = 0; i < size; i++) {
  323 |         orders.push({
  324 |           uuid: crypto.randomUUID(),
  325 |           items: [{ menu_id: 1, quantity: 2, price: 12000 }],
  326 |           paymentMethod: 'cash',
  327 |           customerName: 'Batch Test',
  328 |           isMahasiswa: false,
  329 |           total: 24000,
  330 |           createdAt: new Date().toISOString(),
  331 |         });
  332 |       }
  333 | 
  334 |       // Send batch sync request
  335 |       const startTime = Date.now();
  336 |       const resp = await page.request.post(`${BASE}/sync-orders`, {
  337 |         data: { orders }
  338 |       });
  339 |       const elapsed = Date.now() - startTime;
> 340 |       const body = await resp.json();
      |                    ^ SyntaxError: Unexpected token '<', "<!DOCTYPE "... is not valid JSON
  341 | 
  342 |       expect(resp.status()).toBe(200);
  343 |       expect(body.summary.synced).toBe(size);
  344 |       expect(body.summary.failed).toBe(0);
  345 | 
  346 |       const perOrder = elapsed / size;
  347 |       console.log(`Batch ${size}: ${elapsed} ms total, ${perOrder.toFixed(1)} ms/order`);
  348 | 
  349 |       test.info().annotations.push({
  350 |         type: `Batch ${size}`,
  351 |         description: `${elapsed} ms total, ${perOrder.toFixed(1)} ms/order`
  352 |       });
  353 |     });
  354 |   }
  355 | 
  356 |   test('4z. Projection to Vercel Hobby (60s limit)', async ({ page }) => {
  357 |     // Use average time from batch 50 to project max order count
  358 |     const orders = [];
  359 |     for (let i = 0; i < 50; i++) {
  360 |       orders.push({
  361 |         uuid: crypto.randomUUID(),
  362 |         items: [{ menu_id: 1, quantity: 1, price: 12000 }],
  363 |         paymentMethod: 'cash',
  364 |         customerName: 'Projection Test',
  365 |         isMahasiswa: false,
  366 |         total: 12000,
  367 |         createdAt: new Date().toISOString(),
  368 |       });
  369 |     }
  370 | 
  371 |     const start = Date.now();
  372 |     await page.request.post(`${BASE}/sync-orders`, { data: { orders } });
  373 |     const elapsed = Date.now() - start;
  374 |     const perOrder = elapsed / 50;
  375 | 
  376 |     const vercelBudget = 60000; // ms
  377 |     const coldStart = 250; // ms
  378 |     const safetyMargin = 5000; // ms
  379 |     const available = vercelBudget - coldStart - safetyMargin;
  380 |     const maxTheoretical = Math.floor(available / perOrder);
  381 |     const safeMax = Math.floor(maxTheoretical * 0.7);
  382 | 
  383 |     console.log(`\n=== Proyeksi Vercel Hobby (max 60s) ===`);
  384 |     console.log(`Time per order (dari benchmark): ${perOrder.toFixed(1)} ms`);
  385 |     console.log(`Budget available: ${available} ms`);
  386 |     console.log(`Max order teoretis: ~${maxTheoretical}`);
  387 |     console.log(`Batas aman (70%): ~${safeMax}`);
  388 |     console.log(`Chunk ideal: ${Math.min(100, safeMax)} order per request`);
  389 | 
  390 |     test.info().annotations.push({
  391 |       type: 'Vercel Projection',
  392 |       description: `Per order: ${perOrder.toFixed(1)}ms, Max: ${maxTheoretical}, Safe: ${safeMax}`
  393 |     });
  394 |   });
  395 | });
  396 | 
```