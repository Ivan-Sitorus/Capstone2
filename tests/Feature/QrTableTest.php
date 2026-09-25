<?php

namespace Tests\Feature;

use App\Models\CafeTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_token_is_auto_generated_as_uuid_v4(): void
    {
        $table = CafeTable::create(['table_number' => 1]);

        $this->assertNotNull($table->qr_token);
        $this->assertTrue(Str::isUuid($table->qr_token), 'qr_token harus UUID yang valid.');
        $this->assertSame('4', $table->qr_token[14], 'qr_token harus UUID versi 4.');
    }

    public function test_qr_url_is_derived_from_token(): void
    {
        $table = CafeTable::create(['table_number' => 2]);

        $this->assertSame(
            route('customer.identity', ['table' => $table->qr_token]),
            $table->qr_url
        );
    }

    public function test_qr_images_render_from_derived_url(): void
    {
        $table = CafeTable::create(['table_number' => 3]);

        $this->assertStringContainsString('<svg', $table->qr_svg);

        $this->assertStringStartsWith('data:image/png;base64,', $table->qr_png_data_uri);

        $this->assertSame("\x89PNG", substr($table->generatePngDownload(), 0, 4));
    }

    public function test_qr_entry_redirects_to_identity_with_token(): void
    {
        $table = CafeTable::create(['table_number' => 4]);

        $this->get('/order?table=' . $table->qr_token)
            ->assertRedirect(route('customer.identity', ['table' => $table->qr_token]));
    }

    public function test_identity_page_resolves_token_and_rejects_unknown(): void
    {
        $table = CafeTable::create(['table_number' => 5]);

        $this->get(route('customer.identity', ['table' => $table->qr_token]))
            ->assertOk();

        $this->get(route('customer.identity', ['table' => (string) Str::uuid()]))
            ->assertNotFound();
    }
}
