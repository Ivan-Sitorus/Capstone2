<?php

namespace Tests\Feature\Receipt;

use App\Events\OrderQrisReviewed;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cashier = User::factory()->create(['role' => 'cashier']);
    }

    private function cashierActingAs(): self
    {
        return $this->actingAs($this->cashier, 'web');
    }

    private function createProofSubmittedOrder(array $overrides = []): Order
    {
        return Order::factory()->pending()->create(array_merge([
            'qris_status' => 'proof_submitted',
            'payment_method' => 'qris',
            'payment_proof' => 'qris-proofs/fake-proof.webp',
            'is_paid' => false,
        ], $overrides));
    }

    /**
     * Accepting a proof broadcasts, sets status to 'diproses', and clears payment_proof.
     */
    public function test_accepts_proof_and_broadcasts(): void
    {
        Event::fake([OrderQrisReviewed::class]);
        Storage::fake('public');
        Storage::disk('public')->put('qris-proofs/fake-proof.webp', 'fake-content');

        $order = $this->createProofSubmittedOrder();

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/accept");

        $response->assertOk();
        $response->assertJson(['message' => 'Bukti QRIS diterima. Pesanan diproses.']);

        $order->refresh();
        $this->assertSame('accepted', $order->qris_status);
        $this->assertSame(Order::STATUS_DIPROSES, $order->status);
        $this->assertNull($order->payment_proof);
        $this->assertSame($this->cashier->id, $order->cashier_id);

        Storage::disk('public')->assertMissing('qris-proofs/fake-proof.webp');

        Event::assertDispatched(OrderQrisReviewed::class, function (OrderQrisReviewed $event) use ($order) {
            return $event->order->id === $order->id
                && $event->decision === 'accepted'
                && $event->reason === null;
        });
    }

    /**
     * Rejecting a proof sets is_paid to false, saves reason, and deletes the proof file.
     */
    public function test_rejects_proof_and_sets_is_paid_false(): void
    {
        Event::fake([OrderQrisReviewed::class]);
        Storage::fake('public');
        Storage::disk('public')->put('qris-proofs/fake-proof.webp', 'fake-content');

        $order = $this->createProofSubmittedOrder(['is_paid' => true]);

        $reason = 'Gambar bukti tidak sesuai dengan nominal transaksi.';
        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/reject", [
                'reason' => $reason,
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Bukti QRIS ditolak.']);

        $order->refresh();
        $this->assertSame('rejected', $order->qris_status);
        $this->assertFalse($order->is_paid, 'is_paid must be set to false on reject');
        $this->assertSame($reason, $order->rejection_note);
        $this->assertNull($order->payment_proof);

        Storage::disk('public')->assertMissing('qris-proofs/fake-proof.webp');

        Event::assertDispatched(OrderQrisReviewed::class, function (OrderQrisReviewed $event) use ($order, $reason) {
            return $event->order->id === $order->id
                && $event->decision === 'rejected'
                && $event->reason === $reason;
        });
    }

    /**
     * Reject endpoint requires the reason field.
     */
    public function test_reject_requires_reason_field(): void
    {
        Storage::fake('public');
        $order = $this->createProofSubmittedOrder();

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/reject", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    /**
     * Requesting resubmit saves the reason and deletes the proof file.
     */
    public function test_requests_resubmit_with_reason(): void
    {
        Event::fake([OrderQrisReviewed::class]);
        Storage::fake('public');
        Storage::disk('public')->put('qris-proofs/fake-proof.webp', 'fake-content');

        $order = $this->createProofSubmittedOrder();

        $reason = 'Tangkapan layar buram, silakan foto ulang dengan jelas.';
        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/resubmit", [
                'reason' => $reason,
            ]);

        $response->assertOk();
        $response->assertJson(['message' => 'Pengunggahan ulang bukti QRIS diminta.']);

        $order->refresh();
        $this->assertSame('resubmit_requested', $order->qris_status);
        $this->assertSame($reason, $order->rejection_note);
        $this->assertNull($order->payment_proof);

        Storage::disk('public')->assertMissing('qris-proofs/fake-proof.webp');

        Event::assertDispatched(OrderQrisReviewed::class, function (OrderQrisReviewed $event) use ($order, $reason) {
            return $event->order->id === $order->id
                && $event->decision === 'resubmit_requested'
                && $event->reason === $reason;
        });
    }

    /**
     * Resubmit endpoint requires the reason field.
     */
    public function test_resubmit_requires_reason_field(): void
    {
        Storage::fake('public');
        $order = $this->createProofSubmittedOrder();

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/resubmit", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['reason']);
    }

    /**
     * Guard: accept fails with 409 when qris_status is not 'proof_submitted'.
     */
    public function test_guards_accept_when_status_not_proof_submitted(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'payment_method' => 'qris',
        ]);

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/accept");

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Bukti QRIS tidak dalam status review.']);
    }

    /**
     * Guard: reject fails with 409 when qris_status is not 'proof_submitted'.
     */
    public function test_guards_reject_when_status_not_proof_submitted(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => 'accepted',
            'payment_method' => 'qris',
        ]);

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/reject", [
                'reason' => 'Test reason.',
            ]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Bukti QRIS tidak dalam status review.']);
    }

    /**
     * Guard: resubmit fails with 409 when qris_status is not 'proof_submitted'.
     */
    public function test_guards_resubmit_when_status_not_proof_submitted(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => 'rejected',
            'payment_method' => 'qris',
        ]);

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/resubmit", [
                'reason' => 'Test reason.',
            ]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Bukti QRIS tidak dalam status review.']);
    }

    /**
     * Accepting proof does not set is_paid to true (confirmation is separate).
     */
    public function test_accept_does_not_set_is_paid_true(): void
    {
        Event::fake([OrderQrisReviewed::class]);
        Storage::fake('public');

        $order = $this->createProofSubmittedOrder(['is_paid' => false]);

        $response = $this->cashierActingAs()
            ->postJson("/kasir/pesanan/{$order->id}/qris/accept");

        $response->assertOk();
        $order->refresh();

        $this->assertFalse($order->is_paid, 'is_paid should remain false; payment confirmation is separate');
        $this->assertSame('accepted', $order->qris_status);
    }

    /**
     * Unauthenticated users cannot access QRIS review endpoints.
     */
    public function test_rejects_unauthenticated_access(): void
    {
        $order = Order::factory()->pending()->create([
            'qris_status' => 'proof_submitted',
        ]);

        $response = $this->postJson("/kasir/pesanan/{$order->id}/qris/accept");
        $response->assertStatus(401);

        $response = $this->postJson("/kasir/pesanan/{$order->id}/qris/reject", ['reason' => 'x']);
        $response->assertStatus(401);

        $response = $this->postJson("/kasir/pesanan/{$order->id}/qris/resubmit", ['reason' => 'x']);
        $response->assertStatus(401);
    }
}
