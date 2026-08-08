<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_returns_successful_response(): void
    {
        $response = $this->get('/pelanggan/menu');

        $response->assertOk();
    }
}
