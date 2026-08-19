<?php

namespace Tests\Unit;

use App\Models\BaseModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_base_model_uses_ulids(): void
    {
        $model = new class extends BaseModel {};

        $this->assertFalse($model->getIncrementing());
        $this->assertSame('string', $model->getKeyType());
    }
}