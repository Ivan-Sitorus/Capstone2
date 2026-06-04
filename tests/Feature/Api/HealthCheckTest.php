<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * HEAD /api/ping returns 200 with X-Health header.
     */
    public function test_health_check_returns_200_with_header(): void
    {
        $response = $this->head('/api/ping');

        $response->assertStatus(200);
        $response->assertHeader('X-Health', 'ok');
    }

    /**
     * GET /api/ping also works (for curl convenience).
     */
    public function test_health_check_via_get_returns_200(): void
    {
        $response = $this->get('/api/ping');

        $response->assertStatus(200);
        $response->assertHeader('X-Health', 'ok');
    }

    /**
     * Health check is public — no auth required.
     */
    public function test_health_check_works_without_authentication(): void
    {
        $response = $this->head('/api/ping');

        $response->assertStatus(200);
        $response->assertHeader('X-Health', 'ok');
    }
}
