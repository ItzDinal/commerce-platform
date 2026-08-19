<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_can_be_created(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);
    }

    public function test_category_uses_ulid(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $this->assertNotEmpty($category->id);
        $this->assertSame(26, strlen($category->id));
        $this->assertFalse($category->getIncrementing());
        $this->assertSame('string', $category->getKeyType());
    }

    public function test_category_can_be_soft_deleted(): void
    {
        $category = Category::create([
            'name' => 'Sarees',
            'slug' => 'sarees',
        ]);

        $category->delete();

        $this->assertSoftDeleted('categories', [
            'id' => $category->id,
        ]);

        // $this->assertDatabaseMissing('categories', [
        //     'id' => $category->id,
        // ]);
    }
}