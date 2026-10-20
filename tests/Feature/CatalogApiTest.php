<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_categories_and_products(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $categoryResponse = $this->postJson('/api/categories', [
            'name' => 'Pakaian Pria',
            'description' => 'Koleksi pakaian pria.',
        ]);

        $categoryResponse
            ->assertCreated()
            ->assertJsonPath('name', 'Pakaian Pria')
            ->assertJsonPath('slug', 'pakaian-pria');

        $productResponse = $this->postJson('/api/products', [
            'category_id' => $categoryResponse->json('id'),
            'name' => 'Kaos Premium',
            'description' => 'Kaos katun premium.',
            'price' => 125000,
            'stock' => 10,
            'status' => 'active',
            'images' => [
                UploadedFile::fake()->image('kaos-premium-depan.jpg'),
                UploadedFile::fake()->image('kaos-premium-belakang.jpg'),
            ],
        ]);

        $productResponse
            ->assertCreated()
            ->assertJsonPath('name', 'Kaos Premium')
            ->assertJsonPath('category.name', 'Pakaian Pria')
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('sku', 'PRD-000001')
            ->assertJsonCount(2, 'images')
            ->assertJsonPath('images.0.is_thumbnail', true);

        Storage::disk('public')->assertExists($productResponse->json('images.0.path'));
        Storage::disk('public')->assertExists($productResponse->json('images.1.path'));

        $this->getJson('/api/products?search=Premium')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'PRD-000001');
    }

    public function test_catalog_endpoint_requires_a_sanctum_token(): void
    {
        $this->getJson('/api/categories')->assertUnauthorized();
    }

    public function test_public_catalog_only_shows_active_products(): void
    {
        Product::query()->create(['name' => 'Produk Aktif', 'slug' => 'produk-aktif', 'status' => 'active']);
        Product::query()->create(['name' => 'Produk Draf', 'slug' => 'produk-draf', 'status' => 'draft']);

        $this->getJson('/api/catalog/products')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'produk-aktif');

        $this->getJson('/api/catalog/products/produk-draf')->assertNotFound();
        $this->getJson('/api/catalog/products/produk-aktif')->assertOk();
    }

    public function test_public_categories_only_include_categories_with_active_products(): void
    {
        $activeCategory = Category::query()->create(['name' => 'Jersey Aktif', 'slug' => 'jersey-aktif']);
        $draftCategory = Category::query()->create(['name' => 'Jersey Draf', 'slug' => 'jersey-draf']);
        Product::query()->create(['category_id' => $activeCategory->id, 'name' => 'Produk Aktif', 'slug' => 'produk-aktif', 'status' => 'active']);
        Product::query()->create(['category_id' => $draftCategory->id, 'name' => 'Produk Draf', 'slug' => 'produk-draf', 'status' => 'draft']);

        $this->getJson('/api/catalog/categories')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.slug', 'jersey-aktif')
            ->assertJsonPath('0.products_count', 1);
    }
}
