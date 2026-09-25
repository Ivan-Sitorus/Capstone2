"""Dataset B loader — replikasi spesifikasi seeder data-mining repo asli
(ShandovaaGame/Capstone_POS_Cafe) ke schema POSMine (DB pos_cafe).

Menu, resep, bahan, dan harga modal diambil dari:
  - MenuSeeder, CategorySeeder, RecipeIngredientSeeder, HargaModalSeeder
  - AssociationHistorySeeder (888 order multi-item, 2025-01-01..2025-04-10)
  - PredictionHistorySeeder (22 menu x 253 hari = 5566 order, 2025-08-01..2026-04-10)

Catatan adaptasi (didokumentasikan di laporan):
  - status 'selesai' -> 'completed' (enum POSMine).
  - harga_modal -> menus.cost_price, dan disnapshot ke order_items.cost_price.
  - daily_ingredient_usages tidak dibuat; pipeline bahan baku POSMine menurunkan
    pemakaian dari resep (menu_ingredients) secara langsung.
"""
import random
import uuid
from datetime import datetime, timedelta

import psycopg2

DB = dict(host="pgsql", port=5432, dbname="pos_cafe", user="postgres", password="postgres")

INGREDIENTS = [
    ("Bubuk Kopi", "gram", 1000, 10000, 200), ("Susu Cair", "ml", 5000, 100000, 15),
    ("Sirup Gula", "ml", 1000, 20000, 10), ("Teh Celup", "pcs", 100, 3000, 300),
    ("Krimer", "gram", 500, 15000, 50), ("Tepung Terigu", "gram", 1000, 30000, 12),
    ("Telur Ayam", "pcs", 50, 1000, 2500), ("Minyak Goreng", "ml", 2000, 30000, 25),
    ("Bawang Merah", "gram", 500, 15000, 30), ("Garam", "gram", 300, 10000, 5),
    ("Gula Pasir", "gram", 2000, 50000, 15), ("Coklat Bubuk", "gram", 500, 10000, 100),
    ("Mentega", "gram", 300, 8000, 80), ("Keju Parut", "gram", 300, 6000, 120),
    ("Sirup Vanila", "ml", 300, 8000, 100), ("Bubuk Matcha", "gram", 200, 5000, 300),
    ("Jeruk Nipis", "pcs", 50, 500, 1000), ("Pisang", "pcs", 50, 1000, 1500),
    ("Tempe", "pcs", 30, 500, 2500), ("Kentang", "kg", 5, 100, 20000),
    ("Mie", "gram", 1000, 50000, 15), ("Nasi", "gram", 3000, 200000, 5),
    ("Ayam", "gram", 1000, 30000, 80), ("Kecap Manis", "ml", 500, 15000, 25),
]

CATEGORIES = ["Coffee Base", "Tea Base", "Lime Base", "Chocolatos Base", "Snack",
              "Indomie Base", "Nasi Goreng", "Nasi Telur", "Ayam Geprek"]

MENUS = [
    ("Coffee Base", "Espresso", 10000, 2000), ("Coffee Base", "Americano Panas", 10000, 2000),
    ("Coffee Base", "Es Americano", 12000, 2000), ("Coffee Base", "Kopi Susu", 14000, 2000),
    ("Tea Base", "Teh Tawar", 3000, 1000), ("Tea Base", "Teh Manis", 4000, 1000),
    ("Tea Base", "Teh Susu", 7000, 2000), ("Lime Base", "Jeruk Nipis", 5000, 1000),
    ("Lime Base", "Teh Jeruk (Lime Tea)", 6000, 1000), ("Chocolatos Base", "Full Chocolate", 8000, 2000),
    ("Chocolatos Base", "Matcha", 8000, 2000), ("Chocolatos Base", "Vanilla Latte", 8000, 2000),
    ("Chocolatos Base", "Creamy Chocolatey", 8000, 2000), ("Snack", "Pisang Coklat Keju", 10000, 2000),
    ("Snack", "Tempe Mendoan", 8000, 2000), ("Snack", "Kentang (French Fries)", 12000, 2000),
    ("Indomie Base", "Mie Goreng Telur", 10000, 1000), ("Indomie Base", "Mie Rebus Telur", 10000, 1000),
    ("Nasi Goreng", "Nasgor Telur", 12000, 2000), ("Nasi Goreng", "Nasgor Ayam/Udang", 17000, 2000),
    ("Nasi Telur", "Nasi Telur Saus", 9000, 1000), ("Nasi Telur", "Nasi Telur Kecap", 8000, 1000),
    ("Ayam Geprek", "Nasi Ayam Geprek", 14000, 2000),
]

HARGA_MODAL = {
    "espresso": 2500, "americano panas": 2500, "es americano": 7200, "kopi susu": 3000,
    "teh tawar": 1000, "teh manis": 2400, "teh susu": 2500, "jeruk nipis": 2000,
    "teh jeruk (lime tea)": 2500, "full chocolate": 4800, "matcha": 4800, "vanilla latte": 4800,
    "creamy chocolatey": 4800, "pisang coklat keju": 5000, "tempe mendoan": 4800,
    "kentang (french fries)": 7200, "mie goreng telur": 8000, "mie rebus telur": 8000,
    "nasgor telur": 7200, "nasgor ayam/udang": 11000, "nasi telur saus": 6000,
    "nasi telur kecap": 5500, "nasi ayam geprek": 8400,
}

RECIPES = {
    "Espresso": {"Bubuk Kopi": 10}, "Americano Panas": {"Bubuk Kopi": 10},
    "Es Americano": {"Bubuk Kopi": 10, "Sirup Gula": 20},
    "Kopi Susu": {"Bubuk Kopi": 10, "Susu Cair": 150, "Sirup Gula": 25},
    "Teh Tawar": {"Teh Celup": 1}, "Teh Manis": {"Teh Celup": 1, "Gula Pasir": 15},
    "Teh Susu": {"Teh Celup": 1, "Susu Cair": 100, "Gula Pasir": 10},
    "Jeruk Nipis": {"Jeruk Nipis": 2, "Gula Pasir": 20},
    "Teh Jeruk (Lime Tea)": {"Teh Celup": 1, "Jeruk Nipis": 1, "Gula Pasir": 15},
    "Full Chocolate": {"Coklat Bubuk": 20, "Susu Cair": 150, "Gula Pasir": 15},
    "Matcha": {"Bubuk Matcha": 5, "Susu Cair": 150, "Gula Pasir": 15},
    "Vanilla Latte": {"Bubuk Kopi": 10, "Susu Cair": 150, "Sirup Vanila": 15, "Gula Pasir": 5},
    "Creamy Chocolatey": {"Coklat Bubuk": 20, "Susu Cair": 100, "Krimer": 30, "Gula Pasir": 15},
    "Pisang Coklat Keju": {"Pisang": 2, "Coklat Bubuk": 10, "Keju Parut": 20, "Tepung Terigu": 30, "Mentega": 10},
    "Tempe Mendoan": {"Tempe": 1, "Tepung Terigu": 50, "Bawang Merah": 10, "Garam": 3, "Minyak Goreng": 30},
    "Kentang (French Fries)": {"Kentang": 0.15, "Garam": 3, "Minyak Goreng": 50},
    "Mie Goreng Telur": {"Mie": 85, "Telur Ayam": 1, "Minyak Goreng": 15, "Bawang Merah": 10, "Garam": 2},
    "Mie Rebus Telur": {"Mie": 85, "Telur Ayam": 1, "Bawang Merah": 5, "Garam": 2},
    "Nasgor Telur": {"Nasi": 200, "Telur Ayam": 1, "Minyak Goreng": 20, "Bawang Merah": 15, "Garam": 3, "Kecap Manis": 10},
    "Nasgor Ayam/Udang": {"Nasi": 200, "Telur Ayam": 1, "Ayam": 100, "Minyak Goreng": 20, "Bawang Merah": 15, "Garam": 3, "Kecap Manis": 10},
    "Nasi Telur Saus": {"Nasi": 200, "Telur Ayam": 2, "Minyak Goreng": 15, "Garam": 3},
    "Nasi Telur Kecap": {"Nasi": 200, "Telur Ayam": 2, "Kecap Manis": 15, "Minyak Goreng": 10, "Garam": 3},
    "Nasi Ayam Geprek": {"Nasi": 200, "Ayam": 150, "Minyak Goreng": 50, "Bawang Merah": 15, "Garam": 5, "Tepung Terigu": 30},
}

ASSOC_TEMPLATES = [
    (["Kopi Susu", "Kentang (French Fries)"], 65), (["Es Americano", "Pisang Coklat Keju"], 65),
    (["Nasi Ayam Geprek", "Es Americano"], 65), (["Nasgor Telur", "Kopi Susu"], 65),
    (["Teh Manis", "Mie Goreng Telur"], 65), (["Matcha", "Tempe Mendoan"], 65),
    (["Vanilla Latte", "Kentang (French Fries)"], 65), (["Teh Jeruk (Lime Tea)", "Nasgor Telur"], 65),
    (["Americano Panas", "Pisang Coklat Keju"], 30), (["Full Chocolate", "Kentang (French Fries)"], 28),
    (["Mie Rebus Telur", "Teh Susu"], 26), (["Teh Manis", "Tempe Mendoan"], 25),
    (["Kopi Susu", "Pisang Coklat Keju"], 25), (["Creamy Chocolatey", "Pisang Coklat Keju"], 24),
    (["Espresso", "Kentang (French Fries)"], 22), (["Nasi Telur Saus", "Teh Manis"], 20),
    (["Kopi Susu", "Kentang (French Fries)", "Pisang Coklat Keju"], 15),
    (["Es Americano", "Nasi Ayam Geprek", "Tempe Mendoan"], 12),
    (["Matcha", "Kentang (French Fries)", "Pisang Coklat Keju"], 12),
    (["Teh Manis", "Mie Goreng Telur", "Tempe Mendoan"], 10),
    (["Vanilla Latte", "Nasi Ayam Geprek", "Tempe Mendoan"], 10),
    (["Kopi Susu", "Nasgor Telur", "Tempe Mendoan"], 8),
    (["Nasgor Ayam/Udang", "Kopi Susu"], 18), (["Kopi Susu", "Tempe Mendoan"], 15),
    (["Teh Susu", "Nasgor Telur"], 14), (["Nasi Telur Kecap", "Teh Manis"], 12),
    (["Nasi Ayam Geprek", "Teh Manis"], 12), (["Americano Panas", "Tempe Mendoan"], 10),
    (["Mie Goreng Telur", "Teh Tawar"], 10), (["Espresso", "Pisang Coklat Keju"], 10),
]

PRED_BASE_QTY = {
    "Kopi Susu": 12, "Es Americano": 10, "Americano Panas": 8, "Espresso": 6, "Teh Manis": 9,
    "Teh Susu": 7, "Teh Tawar": 4, "Teh Jeruk (Lime Tea)": 8, "Matcha": 7, "Vanilla Latte": 9,
    "Full Chocolate": 6, "Creamy Chocolatey": 5, "Kentang (French Fries)": 11,
    "Pisang Coklat Keju": 9, "Tempe Mendoan": 8, "Nasgor Telur": 10, "Nasgor Ayam/Udang": 7,
    "Mie Goreng Telur": 8, "Mie Rebus Telur": 6, "Nasi Ayam Geprek": 9,
    "Nasi Telur Saus": 7, "Nasi Telur Kecap": 6,
}


def main():
    rng = random.Random(2025)
    conn = psycopg2.connect(**DB)
    cur = conn.cursor()

    cur.execute("""TRUNCATE order_items, orders, order_payments, menu_ingredients,
                   ingredient_batches, ingredients, menus, menu_categories RESTART IDENTITY CASCADE;""")

    cat_id = {}
    for name in CATEGORIES:
        cur.execute("INSERT INTO menu_categories (name, created_at, updated_at) VALUES (%s, now(), now()) RETURNING id", (name,))
        cat_id[name] = cur.fetchone()[0]

    ing_id, ing_unit = {}, {}
    for name, unit, threshold, stock, cost in INGREDIENTS:
        cur.execute("""INSERT INTO ingredients (name, unit, low_stock_threshold, batch_mode, created_at, updated_at)
                       VALUES (%s,%s,%s,'fifo', now(), now()) RETURNING id""", (name, unit, threshold))
        iid = cur.fetchone()[0]
        ing_id[name], ing_unit[name] = iid, unit
        cur.execute("""INSERT INTO ingredient_batches
            (ingredient_id, quantity, initial_quantity, total_cost, received_at, batch_code, allow_expired_usage, payment_status, created_at, updated_at)
            VALUES (%s,%s,%s,%s, now(), %s, false, 'paid', now(), now())""",
            (iid, stock, stock, int(stock * cost), f"B-{iid}-1"))

    menu_id, menu_price, menu_cost = {}, {}, {}
    for cat, name, price, cashback in MENUS:
        cost = HARGA_MODAL.get(name.lower(), 0)
        cur.execute("""INSERT INTO menus (category_id, name, price, cost_price, discounted_price, status, created_at, updated_at)
                       VALUES (%s,%s,%s,%s,%s,'active', now(), now()) RETURNING id""",
                    (cat_id[cat], name, price, cost, price - cashback))
        mid = cur.fetchone()[0]
        menu_id[name], menu_price[name], menu_cost[name] = mid, price, cost

    for menu, recipe in RECIPES.items():
        if menu not in menu_id:
            continue
        for ine, qty in recipe.items():
            cur.execute("""INSERT INTO menu_ingredients (menu_id, ingredient_id, quantity_used, unit)
                           VALUES (%s,%s,%s,%s)""", (menu_id[menu], ing_id[ine], qty, ing_unit[ine]))

    orders, items = [], []

    def add_order(code, ts, lines):
        total = sum(q * menu_price[m] for m, q in lines)
        orders.append((code, "completed", "cashier", "cash", total, ts, ts, str(uuid.uuid4())))
        for m, q in lines:
            items.append((code, menu_id[m], q, menu_price[m], menu_cost[m], q * menu_price[m], ts, ts))

    start = datetime(2025, 1, 1)
    total_days = 100
    num = 2000
    for names, count in ASSOC_TEMPLATES:
        for _ in range(count):
            day = start + timedelta(days=(num - 2000) % total_days)
            ts = day.replace(hour=rng.randint(8, 20), minute=rng.randint(0, 59), second=0)
            lines = [(n, rng.randint(1, 3)) for n in names if n in menu_id]
            if lines:
                add_order(f"ORD{num}", ts, lines)
            num += 1

    cur_day = datetime(2025, 8, 1)
    end = datetime(2026, 4, 10)
    num = 3000
    while cur_day <= end:
        weekend = cur_day.weekday() >= 5
        for name in PRED_BASE_QTY:
            base = PRED_BASE_QTY[name]
            noise = int(round(base * 0.30))
            weekend_boost = 1.25 if weekend else 1.0
            qty = max(1, int(round((base + rng.randint(-noise, noise)) * weekend_boost)))
            ts = cur_day.replace(hour=rng.randint(8, 21), minute=rng.randint(0, 59), second=0)
            add_order(f"ORD{num}", ts, [(name, qty)])
            num += 1
        cur_day += timedelta(days=1)

    cur.executemany("""INSERT INTO orders
        (order_code,status,order_type,payment_method,total_amount,created_at,updated_at,uuid)
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s)""", orders)
    cur.execute("SELECT id, order_code FROM orders")
    code_to_id = {c: i for i, c in cur.fetchall()}
    cur.executemany("""INSERT INTO order_items
        (order_id,menu_id,item_position,quantity,unit_price,cost_price,subtotal,created_at,updated_at)
        VALUES (%s,%s,%s,%s,%s,%s,%s,%s,%s)""",
        [(code_to_id[c], mid, pos, q, up, cp, sub, ts, ts)
         for pos, (c, mid, q, up, cp, sub, ts, _ts2) in [(i, r) for i, r in enumerate(items)]])

    conn.commit()
    cur.execute("SELECT count(*), min(created_at)::date, max(created_at)::date FROM orders")
    print("orders:", cur.fetchone())
    cur.execute("SELECT count(*) FROM order_items")
    print("order_items:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM menus")
    print("menus:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM ingredients")
    print("ingredients:", cur.fetchone()[0])
    cur.execute("SELECT count(*) FROM menu_ingredients")
    print("menu_ingredients:", cur.fetchone()[0])
    conn.close()


if __name__ == "__main__":
    main()
