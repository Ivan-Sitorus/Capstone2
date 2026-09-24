<?php

return [
    'start_date' => env('SEED_START_DATE', '2025-01-01'),
    'end_date' => env('SEED_END_DATE'),

    'orders_per_day' => (int) env('SEED_ORDERS_PER_DAY', 100),
    'daily_jitter' => (float) env('SEED_DAILY_JITTER', 0.15),
    'weekend_multiplier' => (float) env('SEED_WEEKEND_MULTIPLIER', 1.4),
    'monthly_seasonality' => [
        1 => 1.00,
        2 => 1.00,
        3 => 1.05,
        4 => 1.10,
        5 => 1.00,
        6 => 1.05,
        7 => 1.00,
        8 => 1.00,
        9 => 0.95,
        10 => 1.00,
        11 => 1.00,
        12 => 1.15,
    ],

    'items_min' => (int) env('SEED_ITEMS_MIN', 2),
    'items_max' => (int) env('SEED_ITEMS_MAX', 6),
    'qty_min' => (int) env('SEED_QTY_MIN', 1),
    'qty_max' => (int) env('SEED_QTY_MAX', 3),

    'student_discount_rate' => (float) env('SEED_STUDENT_RATE', 0.25),

    'payment_mix' => [
        'cash' => 55,
        'qris' => 35,
        'pay_later' => 10,
    ],
    'pay_later_fully_paid_ratio' => 0.60,
    'pay_later_partial_ratio' => 0.25,
    'payable_unpaid_ratio' => 0.30,

    'chunk_days' => (int) env('SEED_CHUNK_DAYS', 7),
    'insert_chunk' => (int) env('SEED_INSERT_CHUNK', 1000),
];
