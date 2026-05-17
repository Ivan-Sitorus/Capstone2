<?php

namespace Tests\Feature\Receipt;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrisWebPCompressionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uploading a PNG proof compresses it to WebP and stores under 500KB.
     */
    public function test_compresses_png_to_webp(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
            'payment_method' => 'qris',
        ]);

        // Create a 200x200 PNG image (real GD-generated image bytes)
        $file = UploadedFile::fake()->image('proof.png', 200, 200);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertSame('proof_submitted', $order->qris_status);
        $this->assertNotNull($order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);

        // Verify stored file is WebP (when GD supports it) and under 500KB
        $storedPath = $order->payment_proof;
        $storedSize = Storage::disk('public')->size($storedPath);

        if (function_exists('imagewebp')) {
            $this->assertStringEndsWith('.webp', $storedPath, 'Should store as WebP format');
        }

        $this->assertLessThan(500000, $storedSize, 'Compressed file must be under 500KB');

        // Verify stored content is a valid image
        $storedContent = Storage::disk('public')->get($storedPath);
        $this->assertNotEmpty($storedContent);
        $imageInfo = getimagesizefromstring($storedContent);
        $this->assertNotFalse($imageInfo, 'Stored file must be a valid image');
    }

    /**
     * Uploading a JPEG proof also compresses and stores correctly.
     */
    public function test_compresses_jpeg_proof(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
            'payment_method' => 'qris',
        ]);

        $file = UploadedFile::fake()->image('proof.jpg', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        Storage::disk('public')->assertExists($order->payment_proof);

        $storedSize = Storage::disk('public')->size($order->payment_proof);
        $this->assertLessThan(500000, $storedSize, 'Compressed JPEG must be under 500KB');
    }

    /**
     * Upload deletes the previous proof file before storing the new one.
     */
    public function test_deletes_old_proof_on_new_upload(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('qris-proofs/old-proof.webp', 'old file content');

        $order = Order::factory()->pending()->create([
            'qris_status' => 'resubmit_requested',
            'resubmit_count' => 1,
            'payment_method' => 'qris',
            'payment_proof' => 'qris-proofs/old-proof.webp',
        ]);

        Storage::disk('public')->assertExists('qris-proofs/old-proof.webp');

        $file = UploadedFile::fake()->image('new-proof.png', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        Storage::disk('public')->assertMissing('qris-proofs/old-proof.webp');
        $this->assertNotSame('qris-proofs/old-proof.webp', $order->payment_proof);
        Storage::disk('public')->assertExists($order->payment_proof);
    }

    /**
     * Large images are compressed to well under the 5MB upload limit.
     */
    public function test_compresses_large_image(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
        ]);

        // Create a large 2000x2000 JPEG just under 5MB
        $file = UploadedFile::fake()->image('large-proof.jpg', 2000, 2000)->size(4500);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $storedSize = Storage::disk('public')->size($order->payment_proof);
        $this->assertLessThan(500000, $storedSize, 'Large image must compress to under 500KB');
    }

    /**
     * The payment_proof column is cleared when set to null after review (not a null upload).
     */
    public function test_payment_proof_set_correctly_after_upload(): void
    {
        Storage::fake('public');

        $order = Order::factory()->pending()->create([
            'qris_status' => null,
            'resubmit_count' => 0,
            'payment_proof' => null,
        ]);

        $file = UploadedFile::fake()->image('proof.png', 400, 600);

        $response = $this->postJson("/api/order/{$order->id}/qris-proof", [
            'proof' => $file,
        ]);

        $response->assertOk();
        $order->refresh();

        $this->assertNotNull($order->payment_proof);
        $this->assertStringStartsWith('qris-proofs/', $order->payment_proof);
    }
}
