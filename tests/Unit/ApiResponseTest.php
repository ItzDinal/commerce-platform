<?php

namespace Tests\Unit;

use App\Http\Responses\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response(): void
    {
        $response = ApiResponse::success(
            data: ['status' => 'healthy'],
            message: 'API is running.'
        );

        $this->assertSame(200, $response->status());
        $this->assertSame([
            'success' => true,
            'message' => 'API is running.',
            'data' => [
                'status' => 'healthy',
            ],
        ], $response->getData(true));
    }

    public function test_error_response(): void
    {
        $response = ApiResponse::error(
            message: 'Something went wrong.',
            errors: ['test' => ['Error']],
            status: 400
        );

        $this->assertSame(400, $response->status());
        $this->assertSame([
            'success' => false,
            'message' => 'Something went wrong.',
            'errors' => [
                'test' => ['Error'],
            ],
        ], $response->getData(true));
    }
}