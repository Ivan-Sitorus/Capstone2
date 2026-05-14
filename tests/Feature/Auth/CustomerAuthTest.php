<?php

namespace Tests\Feature\Auth;

use App\Models\CafeTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The old /customer/login route was replaced with the Identitas flow.
     * The identitas form is rendered at /order?table={id}.
     * Form submission is client-side (Inertia/React), so there is no
     * server-side POST endpoint for the identitas form.
     */
    public function test_identitas_form_renders_with_table(): void
    {
        $table = CafeTable::factory()->create(['table_number' => 1]);

        $response = $this->get('/order?table='.$table->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pelanggan/Identitas'));
        $this->assertArrayHasKey('table', $response->inertiaProps());
        $this->assertNotNull($response->inertiaProps()['table']);
        $this->assertEquals($table->id, $response->inertiaProps()['table']['id']);
        $this->assertEquals(1, $response->inertiaProps()['table']['table_number']);
    }

    public function test_identitas_form_renders_without_table_shows_qr_scan_message(): void
    {
        $response = $this->get('/order');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pelanggan/Identitas'));
        $this->assertArrayHasKey('table', $response->inertiaProps());
        $this->assertNull($response->inertiaProps()['table']);
    }

    public function test_identitas_form_returns_404_for_table_number_above_10(): void
    {
        $table = CafeTable::factory()->create(['table_number' => 11]);

        $response = $this->get('/order?table='.$table->id);

        $response->assertStatus(404);
    }

    public function test_identitas_form_returns_404_for_nonexistent_table(): void
    {
        $response = $this->get('/order?table=99999');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Pelanggan/Identitas'));
        $this->assertNull($response->inertiaProps()['table']);
    }
}
