<?php

namespace Tests\Feature\Seeding;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BusySeedConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_data_is_consistent(): void
    {
        $this->seed();

        $this->assertGreaterThan(0, DB::table('orders')->count());

        $totalMismatch = DB::selectOne(
            'select count(*) as c from orders o where o.total_amount <> '
            . '(select coalesce(sum(subtotal), 0) from order_items oi where oi.order_id = o.id)'
        );
        $this->assertSame(0, (int) $totalMismatch->c, 'total_amount harus sama dengan jumlah subtotal');

        $offShift = DB::table('orders as o')->whereNotExists(function ($q) {
            $q->select(DB::raw(1))
                ->from('cashier_histories as ch')
                ->whereColumn('ch.user_id', 'o.cashier_id')
                ->whereColumn('o.created_at', '>=', 'ch.started_at')
                ->whereColumn('o.created_at', '<=', 'ch.ended_at');
        })->count();
        $this->assertSame(0, $offShift, 'setiap order harus dibuat oleh kasir yang sedang shift');

        $this->assertSame(0, DB::table('ingredient_batches')->where('quantity', '<', 0)->count(), 'stok tidak boleh negatif');

        $stockMismatch = DB::selectOne(
            'select count(*) as c from ('
            . 'select i.id, '
            . 'coalesce((select sum(quantity_change) from stock_movements m where m.ingredient_id = i.id), 0) mv, '
            . 'coalesce((select sum(quantity) from ingredient_batches b where b.ingredient_id = i.id), 0) st '
            . 'from ingredients i) z where round(mv, 3) <> round(st, 3)'
        );
        $this->assertSame(0, (int) $stockMismatch->c, 'jumlah movement harus sama dengan stok');

        $orderOverpay = DB::selectOne(
            'select count(*) as c from (select order_id, sum(amount) s from order_payments group by order_id) x '
            . 'join orders o on o.id = x.order_id where x.s > o.total_amount'
        );
        $this->assertSame(0, (int) $orderOverpay->c, 'pembayaran piutang tidak boleh melebihi total');

        $batchOverpay = DB::selectOne(
            'select count(*) as c from (select ingredient_batch_id, sum(amount) s from batch_payments group by ingredient_batch_id) x '
            . 'join ingredient_batches b on b.id = x.ingredient_batch_id where x.s > b.total_cost'
        );
        $this->assertSame(0, (int) $batchOverpay->c, 'pembayaran utang tidak boleh melebihi total biaya batch');

        $badItems = DB::selectOne(
            'select count(*) as c from (select order_id, count(*) n from order_items group by order_id) x where n < 2 or n > 6'
        );
        $this->assertSame(0, (int) $badItems->c, 'tiap order harus memiliki 2-6 item');
    }
}
