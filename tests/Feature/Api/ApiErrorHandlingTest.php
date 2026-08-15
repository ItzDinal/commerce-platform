<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
{
    public function test_api_returns_standard_404_response(): void
    {
        $response = $this->getJson('/api/v1/does-not-exist');

        $response
            ->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Resource not found.',
                'errors' => [],
            ]);
    }
}