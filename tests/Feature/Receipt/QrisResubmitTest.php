<?php

namespace Tests\Feature\Receipt;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisResubmitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Upload is allowed when qris_status is 'resubmit_requested' and count < 3.
     */
    public function test_allows_upload_when_resubmit_requested(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => 'resubmit_requested',
            'resubmit_count' => 1,
            'payment_proof' => null,
            'rejection_note' => 'Gambar buram, ulangi',
        ]);

        $file = UploadedFile::fake()->image('proof.jpg', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertSame(2, $order->resubmit_count);
        $this->assertSame('proof_submitted', $order->qris_status);
        $this->assertNull($order->rejection_note);
        $this->assertNotNull($order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    /**
     * Upload is blocked when resubmit_count >= 3.
     */
    public function test_blocks_upload_when_resubmit_count_exceeded(): void
    {
        Storage::fake('public');

        // count=2 triggers the block after increment (2→3, then check >= 3)
        $order = Order::factory()->pending()->create([
            'qris_status' => 'resubmit_requested',
            'resubmit_count' => 2,
            'payment_proof' => null,
        ]);

        $file = UploadedFile::fake()->image('proof.jpg', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Batas kirim ulang tercapai (maks 3x)']);

        $order->refresh();
        // Controller increments BEFORE checking limit: 2→3, then blocks
        $this->assertSame(3, $order->resubmit_count);
        $this->assertNull($order->payment_proof);
    }

    /**
     * Resubmit count increments from 1 to 2 on successful upload.
     * (With count=2, the increment puts it to 3 which triggers the block.)
     */
    public function test_increments_resubmit_count_on_resubmit_upload(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => 'resubmit_requested',
            'resubmit_count' => 1,
            'payment_proof' => null,
        ]);

        $file = UploadedFile::fake()->image('proof.png', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertSame(2, $order->resubmit_count);
        $this->assertSame('proof_submitted', $order->qris_status);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    /**
     * Upload on a non-pending order returns 409.
     */
    public function test_rejects_upload_when_order_not_pending(): void
    {
        Storage::fake('public');

        $order = Order::factory()->diproses()->create([
            'qris_status' => 'resubmit_requested',
            'resubmit_count' => 0,
        ]);

        $file = UploadedFile::fake()->image('proof.jpg', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Status pesanan tidak valid.']);
    }

    /**
     * Upload with invalid file type returns validation error.
     */
    public function test_rejects_invalid_file_type(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 500);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['proof']);
    }

    /**
     * Upload exceeding 5MB returns validation error.
     */
    public function test_rejects_oversized_file(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
        ]);

        $file = UploadedFile::fake()->image('large.jpg', 1000, 1000)->size(6000); // 6MB

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['proof']);
    }

    /**
     * First-time upload (not a resubmit) sets qris_status to 'proof_submitted'.
     */
    public function test_first_upload_sets_proof_submitted(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
            'payment_method' => 'qris',
        ]);

        $file = UploadedFile::fake()->image('proof.png', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertSame('proof_submitted', $order->qris_status);
        $this->assertSame(0, $order->resubmit_count, 'First upload should not increment counter');
        $this->assertSame('qris', $order->payment_method);
    }
}
