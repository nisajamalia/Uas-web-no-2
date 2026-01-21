<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_api_health_endpoint_works(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'status',
                        'version',
                        'environment',
                    ],
                    'timestamp',
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'SAKTI Mini API is running',
                    'data' => [
                        'status' => 'ok',
                        'version' => '1.0.0',
                    ],
                ]);
    }
}