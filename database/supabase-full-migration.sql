-- ============================================================
-- W9 Cafe POS - Full Database Schema for Supabase
-- Generated from Laravel Migrations
-- Execute this in Supabase Dashboard > SQL Editor
-- ============================================================

-- 1. Users
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP(0) WITHOUT TIME ZONE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL DEFAULT 'customer',
    phone VARCHAR(20) DEFAULT NULL,
    is_student_verified BOOLEAN DEFAULT false,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 2. Cache
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- 3. Jobs
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER DEFAULT NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS jobs_queue_index ON jobs(queue);

-- 4. Categories
CREATE TABLE IF NOT EXISTS categories (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT true,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 5. Menus
CREATE TABLE IF NOT EXISTS menus (
    id BIGSERIAL PRIMARY KEY,
    category_id BIGINT REFERENCES categories(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_available BOOLEAN DEFAULT true,
    is_student_discount BOOLEAN DEFAULT false,
    student_price DECIMAL(10,2) DEFAULT NULL,
    is_stock_calculated BOOLEAN DEFAULT false,
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 6. Cafe Tables
CREATE TABLE IF NOT EXISTS cafe_tables (
    id BIGSERIAL PRIMARY KEY,
    table_number INTEGER NOT NULL UNIQUE,
    qr_code VARCHAR(500) DEFAULT NULL UNIQUE,
    is_available BOOLEAN DEFAULT true,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 7. Orders
CREATE TABLE IF NOT EXISTS orders (
    id BIGSERIAL PRIMARY KEY,
    order_code VARCHAR(50) NOT NULL UNIQUE,
    table_id BIGINT REFERENCES cafe_tables(id) ON DELETE RESTRICT,
    customer_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    cashier_id BIGINT REFERENCES users(id) ON DELETE SET NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    order_type VARCHAR(255) NOT NULL DEFAULT 'qr',
    payment_status VARCHAR(255) NOT NULL DEFAULT 'unpaid',
    total_amount DECIMAL(15,2) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    processed_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    uuid VARCHAR(255) DEFAULT NULL,
    qris_image VARCHAR(255) DEFAULT NULL,
    qris_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);
CREATE INDEX IF NOT EXISTS orders_status_index ON orders(status);
CREATE INDEX IF NOT EXISTS orders_created_at_index ON orders(created_at);

-- 8. Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    menu_id BIGINT NOT NULL REFERENCES menus(id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(15,2) NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 9. Payments
CREATE TABLE IF NOT EXISTS payments (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    payment_method VARCHAR(255) NOT NULL DEFAULT 'cash',
    payment_gateway VARCHAR(255) DEFAULT 'manual',
    transaction_id VARCHAR(255) DEFAULT NULL UNIQUE,
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'pending',
    paid_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 10. Settings
CREATE TABLE IF NOT EXISTS settings (
    id BIGSERIAL PRIMARY KEY,
    key VARCHAR(255) NOT NULL UNIQUE,
    value TEXT DEFAULT NULL,
    type VARCHAR(50) DEFAULT 'string',
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 11. Ingredients
CREATE TABLE IF NOT EXISTS ingredients (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    unit VARCHAR(255) NOT NULL CHECK (unit IN ('gram','kg','ml','liter','pcs','sachet','sdm','sdt')),
    low_stock_threshold DECIMAL(12,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    batch_mode VARCHAR(255) DEFAULT 'fefo' CHECK (batch_mode IN ('fefo','fifo','custom')),
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 12. Ingredient Batches
CREATE TABLE IF NOT EXISTS ingredient_batches (
    id BIGSERIAL PRIMARY KEY,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    quantity DECIMAL(12,2) NOT NULL,
    expiry_date DATE DEFAULT NULL,
    received_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    cost_per_unit DECIMAL(12,2) DEFAULT 0,
    custom_order INTEGER DEFAULT NULL
);
CREATE INDEX IF NOT EXISTS idx_ingredient_batches_ingredient_expiry ON ingredient_batches(ingredient_id, expiry_date);
CREATE INDEX IF NOT EXISTS idx_ingredient_batches_received ON ingredient_batches(received_at);

-- 13. Menu Ingredients (Pivot)
CREATE TABLE IF NOT EXISTS menu_ingredients (
    id BIGSERIAL PRIMARY KEY,
    menu_id BIGINT NOT NULL REFERENCES menus(id) ON DELETE CASCADE,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    quantity_used DECIMAL(12,2) NOT NULL,
    UNIQUE(menu_id, ingredient_id)
);

-- 14. Stock Adjustments
CREATE TABLE IF NOT EXISTS stock_adjustments (
    id BIGSERIAL PRIMARY KEY,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    adjustment_type VARCHAR(255) NOT NULL CHECK (adjustment_type IN ('increase','decrease')),
    quantity DECIMAL(12,2) NOT NULL,
    quantity_before DECIMAL(12,2) DEFAULT NULL,
    quantity_after DECIMAL(12,2) DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    reported_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    adjusted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 15. Stock Movements (IMMUTABLE)
CREATE TABLE IF NOT EXISTS stock_movements (
    id BIGSERIAL PRIMARY KEY,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    ingredient_batch_id BIGINT REFERENCES ingredient_batches(id) ON DELETE SET NULL,
    order_id BIGINT REFERENCES orders(id) ON DELETE SET NULL,
    order_item_id BIGINT REFERENCES order_items(id) ON DELETE SET NULL,
    stock_adjustment_id BIGINT REFERENCES stock_adjustments(id) ON DELETE SET NULL,
    movement_type VARCHAR(255) NOT NULL CHECK (movement_type IN ('purchase','sale','waste','adjustment_increase','adjustment_decrease','correction')),
    source_type VARCHAR(255) DEFAULT NULL,
    source_id VARCHAR(255) DEFAULT NULL,
    quantity_before DECIMAL(12,2) NOT NULL,
    quantity_change DECIMAL(12,2) NOT NULL,
    quantity_after DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) DEFAULT NULL,
    reference VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 16. Incomes
CREATE TABLE IF NOT EXISTS incomes (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT REFERENCES orders(id) ON DELETE SET NULL,
    amount DECIMAL(15,2) NOT NULL,
    source VARCHAR(255) DEFAULT 'penjualan',
    description TEXT DEFAULT NULL,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    recorded_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 17. Receivables
CREATE TABLE IF NOT EXISTS receivables (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT REFERENCES orders(id) ON DELETE SET NULL,
    amount DECIMAL(15,2) NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    invoice_date DATE DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 18. Promotions
CREATE TABLE IF NOT EXISTS promotions (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    type VARCHAR(255) NOT NULL,
    value DECIMAL(15,2) NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    is_student_only BOOLEAN DEFAULT false,
    starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 19. Promotion Rules
CREATE TABLE IF NOT EXISTS promotion_rules (
    id BIGSERIAL PRIMARY KEY,
    promotion_id BIGINT NOT NULL REFERENCES promotions(id) ON DELETE CASCADE,
    type VARCHAR(255) NOT NULL,
    value VARCHAR(255) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 20. Applied Promotions
CREATE TABLE IF NOT EXISTS applied_promotions (
    id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    promotion_id BIGINT NOT NULL REFERENCES promotions(id) ON DELETE CASCADE,
    discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 21. Daily Ingredient Usage
CREATE TABLE IF NOT EXISTS daily_ingredient_usages (
    id BIGSERIAL PRIMARY KEY,
    usage_date DATE NOT NULL,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    ingredient_name VARCHAR(255) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    jumlah_digunakan DECIMAL(12,2) DEFAULT 0,
    UNIQUE(usage_date, ingredient_id)
);

-- 22. Unexpected Transactions
CREATE TABLE IF NOT EXISTS unexpected_transactions (
    id BIGSERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    description TEXT DEFAULT NULL,
    category VARCHAR(255) DEFAULT NULL,
    transaction_date DATE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 23. Generated Reports
CREATE TABLE IF NOT EXISTS generated_reports (
    id BIGSERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    parameters JSON DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    generated_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 24. Data Mining Runs
CREATE TABLE IF NOT EXISTS data_mining_runs (
    id BIGSERIAL PRIMARY KEY,
    status VARCHAR(255) DEFAULT 'pending',
    algorithm VARCHAR(255) DEFAULT NULL,
    parameters JSON DEFAULT NULL,
    result JSON DEFAULT NULL,
    started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    error_message TEXT DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 25. Expenses
CREATE TABLE IF NOT EXISTS expenses (
    id BIGSERIAL PRIMARY KEY,
    amount DECIMAL(15,2) NOT NULL,
    category VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    expense_date DATE DEFAULT NULL,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 26. Stock Reports
CREATE TABLE IF NOT EXISTS stock_reports (
    id BIGSERIAL PRIMARY KEY,
    ingredient_id BIGINT NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    reported_by BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    report_type VARCHAR(255) NOT NULL CHECK (report_type IN ('increase','decrease')),
    quantity DECIMAL(12,2) NOT NULL,
    quantity_before DECIMAL(12,2) DEFAULT NULL,
    quantity_after DECIMAL(12,2) DEFAULT NULL,
    reason TEXT NOT NULL,
    status VARCHAR(255) DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
    reviewed_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    rejection_note TEXT DEFAULT NULL,
    reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 27. Cashier Sessions
CREATE TABLE IF NOT EXISTS cashier_sessions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    token VARCHAR(255) DEFAULT NULL,
    logged_in_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    logged_out_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 28. Staff Sessions
CREATE TABLE IF NOT EXISTS staff_sessions (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(255) NOT NULL,
    token VARCHAR(255) DEFAULT NULL,
    logged_in_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    logged_out_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 29. Menu Stocks
CREATE TABLE IF NOT EXISTS menu_stocks (
    id BIGSERIAL PRIMARY KEY,
    menu_id BIGINT NOT NULL UNIQUE REFERENCES menus(id) ON DELETE CASCADE,
    unit VARCHAR(255) NOT NULL CHECK (unit IN ('gram','kg','ml','liter','pcs','sachet','sdm','sdt')),
    low_stock_threshold DECIMAL(12,2) DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    batch_mode VARCHAR(255) DEFAULT 'fefo' CHECK (batch_mode IN ('fefo','fifo','custom')),
    deleted_at TIMESTAMP(0) WITHOUT TIME ZONE,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 30. Menu Stock Batches
CREATE TABLE IF NOT EXISTS menu_stock_batches (
    id BIGSERIAL PRIMARY KEY,
    menu_stock_id BIGINT NOT NULL REFERENCES menu_stocks(id) ON DELETE CASCADE,
    quantity DECIMAL(12,2) NOT NULL,
    expiry_date DATE DEFAULT NULL,
    received_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    cost_per_unit DECIMAL(12,2) DEFAULT 0,
    custom_order INTEGER DEFAULT NULL
);

-- 31. Menu Stock Adjustments
CREATE TABLE IF NOT EXISTS menu_stock_adjustments (
    id BIGSERIAL PRIMARY KEY,
    menu_stock_id BIGINT NOT NULL REFERENCES menu_stocks(id) ON DELETE CASCADE,
    adjustment_type VARCHAR(255) NOT NULL CHECK (adjustment_type IN ('increase','decrease')),
    quantity DECIMAL(12,2) NOT NULL,
    quantity_before DECIMAL(12,2) DEFAULT NULL,
    quantity_after DECIMAL(12,2) DEFAULT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    reported_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    adjusted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- 32. Menu Stock Movements (IMMUTABLE)
CREATE TABLE IF NOT EXISTS menu_stock_movements (
    id BIGSERIAL PRIMARY KEY,
    menu_stock_id BIGINT NOT NULL REFERENCES menu_stocks(id) ON DELETE CASCADE,
    menu_stock_batch_id BIGINT REFERENCES menu_stock_batches(id) ON DELETE SET NULL,
    order_id BIGINT REFERENCES orders(id) ON DELETE SET NULL,
    order_item_id BIGINT REFERENCES order_items(id) ON DELETE SET NULL,
    menu_stock_adjustment_id BIGINT REFERENCES menu_stock_adjustments(id) ON DELETE SET NULL,
    movement_type VARCHAR(255) NOT NULL CHECK (movement_type IN ('purchase','sale','waste','adjustment_increase','adjustment_decrease','correction')),
    source_type VARCHAR(255) DEFAULT NULL,
    source_id VARCHAR(255) DEFAULT NULL,
    quantity_before DECIMAL(12,2) NOT NULL,
    quantity_change DECIMAL(12,2) NOT NULL,
    quantity_after DECIMAL(12,2) NOT NULL,
    unit_cost DECIMAL(12,2) DEFAULT NULL,
    reference VARCHAR(255) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    recorded_by BIGINT REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE,
    updated_at TIMESTAMP(0) WITHOUT TIME ZONE
);

-- ============================================================
-- MIGRATIONS TABLE (so Laravel knows which migrations ran)
-- ============================================================
CREATE TABLE IF NOT EXISTS migrations (
    id SERIAL PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL
);

-- Insert migration records (mark all as completed)
INSERT INTO migrations (migration, batch, id) VALUES
('0001_01_01_000000_create_users_table', 1, 1),
('0001_01_01_000001_create_cache_table', 1, 2),
('0001_01_01_000002_create_jobs_table', 1, 3),
('2025_01_01_000011_create_categories_table', 1, 4),
('2025_01_01_000012_create_menus_table', 1, 5),
('2025_01_01_000013_create_cafe_tables_table', 1, 6),
('2025_01_01_000014_create_orders_table', 1, 7),
('2025_01_01_000015_create_order_items_table', 1, 8),
('2025_01_01_000016_create_payments_table', 1, 9),
('2026_04_01_120401_create_settings_table', 1, 10),
('2026_04_11_000001_create_ingredients_table', 1, 11),
('2026_04_11_000002_create_ingredient_batches_table', 1, 12),
('2026_04_11_000003_create_menu_ingredients_table', 1, 13),
('2026_04_11_000004_create_stock_adjustments_table', 1, 14),
('2026_04_11_000005_create_stock_movements_table', 1, 15),
('2026_04_11_000008_create_incomes_table', 1, 16),
('2026_04_11_000010_create_receivables_table', 1, 17),
('2026_04_11_000012_create_promotions_table', 1, 18),
('2026_04_11_000013_create_promotion_rules_table', 1, 19),
('2026_04_11_000014_create_applied_promotions_table', 1, 20),
('2026_04_15_000001_create_daily_ingredient_usages_table', 1, 21),
('2026_04_20_000001_create_unexpected_transactions_table', 1, 22),
('2026_05_05_000001_change_low_stock_threshold_to_decimal_in_ingredients_table', 1, 23),
('2026_05_08_000002_create_generated_reports_table', 2, 24),
('2026_05_10_000001_create_data_mining_runs_table', 2, 25),
('2026_05_10_060510_add_invoice_date_to_receivables_table', 2, 26),
('2026_05_10_060713_create_expenses_table', 2, 27),
('2026_05_12_000001_add_batch_mode_and_custom_order', 2, 28),
('2026_05_12_000004_add_nim_and_verification_to_users_table', 2, 29),
('2026_05_12_100000_add_nim_and_student_verified_to_users_table', 2, 30),
('2026_05_12_101344_create_stock_reports_table', 2, 31),
('2026_05_12_110000_add_is_student_only_to_promotions_table', 2, 32),
('2026_05_14_061644_add_processed_by_to_orders_table', 2, 33),
('2026_05_14_061645_drop_cashier_sessions_table', 2, 34),
('2026_05_14_080410_drop_nim_and_student_verified_from_users_table', 2, 35),
('2026_05_15_114322_create_cashier_sessions_table', 3, 36),
('2026_05_15_114438_create_kitchen_sessions_table', 3, 37),
('2026_05_15_114911_drop_legacy_session_tables', 3, 38),
('2026_05_15_145108_add_session_id_to_session_tables', 3, 39),
('2026_05_15_170255_create_staff_sessions_table', 3, 40),
('2026_05_15_200044_drop_keuangan_tables_and_view', 3, 41),
('2026_05_17_000001_create_menu_stocks_table', 3, 42),
('2026_05_17_000002_create_menu_stock_batches_table', 3, 43),
('2026_05_17_000003_create_menu_stock_adjustments_table', 3, 44),
('2026_05_17_000004_create_menu_stock_movements_table', 3, 45),
('2026_05_17_155626_add_deleted_at_to_menus_table', 3, 46),
('2026_05_18_000001_add_uuid_and_qris_fields_to_orders_table', 3, 47);
