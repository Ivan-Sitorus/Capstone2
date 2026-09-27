<?php

namespace Tests\Feature\Payment;

use App\Enums\QrisStatus;
use App\Models\Order;
use App\Support\CustomerSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisProofUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    private function makeOrder(array $overrides = []): Order
    {
        return Order::factory()->create(array_merge([
            'phone' => null,
            'status' => 'pending',
            'payment_method' => 'qris',
            'payment_proof' => null,
        ], $overrides));
    }

    private function upload(Order $order)
    {
        return $this->withSession([CustomerSession::ORDERS_KEY => [(string) $order->id]])
            ->post(route('customer.payment.qris-proof', $order), [
                'proof' => UploadedFile::fake()->image('proof.png'),
            ]);
    }

    public function test_first_upload_marks_the_proof_as_submitted(): void
    {
        $order = $this->makeOrder();

        $this->upload($order)->assertOk();

        $fresh = $order->fresh();
        $this->assertSame(QrisStatus::ProofSubmitted, $fresh->qris_status);
        $this->assertSame(0, $fresh->qris_resubmit_attempts);
        $this->assertNotNull($fresh->payment_proof);
        Storage::disk('public')->assertExists($fresh->payment_proof);
    }

    public function test_resubmit_upload_increments_the_attempt_counter(): void
    {
        $order = $this->makeOrder([
            'qris_status' => QrisStatus::ResubmitRequested->value,
            'qris_resubmit_attempts' => 1,
            'rejection_note' => 'Bukti kurang jelas.',
        ]);

        $this->upload($order)->assertOk();

        $fresh = $order->fresh();
        $this->assertSame(QrisStatus::ProofSubmitted, $fresh->qris_status);
        $this->assertSame(2, $fresh->qris_resubmit_attempts);
        $this->assertNull($fresh->rejection_note);
        $this->assertNotNull($fresh->payment_proof);
    }

    public function test_resubmit_cap_is_enforced(): void
    {
        $order = $this->makeOrder([
            'qris_status' => QrisStatus::ResubmitRequested->value,
            'qris_resubmit_attempts' => 3,
        ]);

        $response = $this->upload($order);

        $response->assertStatus(409);
        $this->assertStringContainsString('Batas pengunggahan ulang', $response->json('message'));

        $fresh = $order->fresh();
        $this->assertNull($fresh->payment_proof);
        $this->assertSame(3, $fresh->qris_resubmit_attempts);
        $this->assertSame(QrisStatus::ResubmitRequested, $fresh->qris_status);
    }
}
