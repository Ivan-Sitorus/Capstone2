# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: thesis/offline-sync.spec.ts >> Metrik 3: Maximum Order Capacity >> 3a. Storage estimate per order
- Location: tests/playwright/thesis/offline-sync.spec.ts:265:3

# Error details

```
Error: page.evaluate: Execution context was destroyed, most likely because of a navigation.
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
  1   | import { test, expect } from '@playwright/test';
  2   | import { waitForInertia } from '../helpers';
  3   | 
  4   | const BASE = 'http://127.0.0.1:8080';
  5   | 
  6   | // ── Helper: inject offline orders directly into Dexie.js ──
  7   | async function injectOfflineOrders(page: any, count: number) {
> 8   |   return page.evaluate((n: number) => {
      |               ^ Error: page.evaluate: Execution context was destroyed, most likely because of a navigation.
  9   |     return new Promise<void>((resolve, reject) => {
  10  |       const req = indexedDB.open('w9cafe');
  11  |       req.onsuccess = () => {
  12  |         const db = req.result;
  13  |         const tx = db.transaction(['offlineOrders', 'offlineOrderItems'], 'readwrite');
  14  |         const orderStore = tx.objectStore('offlineOrders');
  15  |         const itemStore = tx.objectStore('offlineOrderItems');
  16  | 
  17  |         const menuItems = ['Kopi Robusta', 'Kopi Latte', 'Teh Manis', 'Roti Bakar', 'Mie Goreng'];
  18  |         let completed = 0;
  19  | 
  20  |         for (let i = 0; i < n; i++) {
  21  |           const uuid = crypto.randomUUID();
  22  |           const itemCount = Math.floor(Math.random() * 3) + 1;
  23  |           const items = [];
  24  |           for (let j = 0; j < itemCount; j++) {
  25  |             const name = menuItems[Math.floor(Math.random() * menuItems.length)];
  26  |             const qty = Math.floor(Math.random() * 3) + 1;
  27  |             const price = 12000;
  28  |             items.push({ menuId: j + 1, name, qty, price, subtotal: qty * price });
  29  |           }
  30  |           const total = items.reduce((s, i) => s + i.subtotal, 0);
  31  |           const payload = { uuid, items, paymentMethod: 'cash', customerName: 'Test Thesis', isMahasiswa: false, total, createdAt: new Date().toISOString() };
  32  | 
  33  |           const addReq = orderStore.add({ uuid, payload: JSON.stringify(payload), status: 'pending_sync', error: null, createdAt: new Date().toISOString() });
  34  |           addReq.onsuccess = () => {
  35  |             items.forEach(item => {
  36  |               itemStore.add({ orderLocalId: addReq.result, ...item });
  37  |             });
  38  |             completed++;
  39  |             if (completed === n) {
  40  |               tx.oncomplete = () => { db.close(); resolve(); };
  41  |             }
  42  |           };
  43  |           addReq.onerror = () => reject(addReq.error);
  44  |         }
  45  |       };
  46  |       req.onerror = () => reject(req.error);
  47  |     });
  48  |   }, count);
  49  | }
  50  | 
  51  | // ── Helper: count orders in Dexie ──
  52  | async function countDexieOrders(page: any, status?: string) {
  53  |   return page.evaluate((s: string | undefined) => {
  54  |     return new Promise<number>((resolve) => {
  55  |       const req = indexedDB.open('w9cafe');
  56  |       req.onsuccess = () => {
  57  |         const db = req.result;
  58  |         const tx = db.transaction('offlineOrders', 'readonly');
  59  |         const store = tx.objectStore('offlineOrders');
  60  |         if (s) {
  61  |           const idx = store.index('status');
  62  |           const countReq = idx.count(s);
  63  |           countReq.onsuccess = () => { db.close(); resolve(countReq.result); };
  64  |         } else {
  65  |           const countReq = store.count();
  66  |           countReq.onsuccess = () => { db.close(); resolve(countReq.result); };
  67  |         }
  68  |       };
  69  |       req.onerror = () => resolve(-1);
  70  |     });
  71  |   }, status);
  72  | }
  73  | 
  74  | // ── Helper: get storage estimate from browser ──
  75  | async function getStorageEstimate(page: any) {
  76  |   return page.evaluate(() => navigator.storage.estimate());
  77  | }
  78  | 
  79  | // ── Helper: clear all Dexie orders ──
  80  | async function clearDexie(page: any) {
  81  |   return page.evaluate(() => {
  82  |     return new Promise<void>((resolve, reject) => {
  83  |       const req = indexedDB.open('w9cafe');
  84  |       req.onsuccess = () => {
  85  |         const db = req.result;
  86  |         const tx = db.transaction(['offlineOrders', 'offlineOrderItems'], 'readwrite');
  87  |         tx.objectStore('offlineOrders').clear();
  88  |         tx.objectStore('offlineOrderItems').clear();
  89  |         tx.oncomplete = () => { db.close(); resolve(); };
  90  |         tx.onerror = () => reject(tx.error);
  91  |       };
  92  |       req.onerror = () => reject(req.error);
  93  |     });
  94  |   });
  95  | }
  96  | 
  97  | // ── Helper: check if online status indicator shows correct text ──
  98  | async function checkStatusIndicator(page: any, text: string) {
  99  |   await expect(page.locator('text=' + text).first()).toBeVisible({ timeout: 10000 });
  100 | }
  101 | 
  102 | // ── Helper: simulate online/offline by dispatching events ──
  103 | async function setOnlineStatus(page: any, online: boolean) {
  104 |   await page.evaluate((on: boolean) => {
  105 |     if (on) {
  106 |       window.dispatchEvent(new Event('online'));
  107 |     } else {
  108 |       window.dispatchEvent(new Event('offline'));
```