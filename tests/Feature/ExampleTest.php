<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Create sample data for testing
        $category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test Description'
        ]);

        Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 10000,
            'category_id' => $category->id,
            'image' => 'test.jpg',
            'is_active' => true,
            'is_featured' => true
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
