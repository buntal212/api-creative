<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardSummaryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_real_dashboard_counts(): void
    {
        $administrator = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Jersey Futsal',
            'slug' => 'jersey-futsal',
        ]);
        $firstProduct = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Jersey Futsal Merah',
            'slug' => 'jersey-futsal-merah',
            'price' => 150000,
            'stock' => 10,
            'status' => 'active',
        ]);
        $secondProduct = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Jersey Futsal Biru',
            'slug' => 'jersey-futsal-biru',
            'price' => 150000,
            'stock' => 8,
            'status' => 'active',
        ]);
        $firstProduct->forceFill(['created_at' => now()->subDays(2)->startOfDay()->addHours(9)])->save();
        $secondProduct->forceFill(['created_at' => now()->startOfDay()->addHours(10)])->save();
        GalleryItem::factory()->count(3)->create();
        GalleryItem::query()->oldest('id')->limit(1)->update([
            'created_at' => null,
            'updated_at' => null,
        ]);
        User::factory()->count(2)->create();

        Sanctum::actingAs($administrator);

        $this->getJson('/api/dashboard/summary')
            ->assertOk()
            ->assertJsonPath('products', 2)
            ->assertJsonPath('categories', 1)
            ->assertJsonPath('gallery_items', 3)
            ->assertJsonPath('users', 3)
            ->assertJsonCount(7, 'product_activity')
            ->assertJsonCount(3, 'recent_activities')
            ->assertJsonStructure([
                'recent_activities' => [
                    '*' => ['id', 'type', 'title', 'detail', 'occurred_at'],
                ],
            ]);
    }
}
