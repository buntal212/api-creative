<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SiteContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_site_contact(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->putJson('/api/site-contact', [
            'whatsapp' => '6282334148314',
            'phone' => '0823-3414-8314',
            'email' => 'halo@creative.test',
            'address' => 'Kota Malang, Jawa Timur',
            'instagram_url' => 'https://instagram.com/creative',
            'business_hours' => 'Senin - Sabtu, 08.00 - 17.00 WIB',
        ])
            ->assertOk()
            ->assertJsonPath('whatsapp', '6282334148314')
            ->assertJsonPath('email', 'halo@creative.test');

        $this->assertDatabaseHas('site_contacts', [
            'whatsapp' => '6282334148314',
            'business_hours' => 'Senin - Sabtu, 08.00 - 17.00 WIB',
        ]);

        $this->getJson('/api/site/contact')
            ->assertOk()
            ->assertJsonPath('phone', '0823-3414-8314');
    }

    public function test_site_contact_update_requires_authentication(): void
    {
        $this->putJson('/api/site-contact', ['email' => 'halo@creative.test'])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_add_gallery_image_and_public_endpoint_only_returns_published_items(): void
    {
        Storage::fake('public');
        Sanctum::actingAs(User::factory()->create());

        $response = $this->post('/api/gallery-items', [
            'title' => 'Jersey Tim Futsal',
            'description' => 'Hasil produksi jersey futsal.',
            'alt_text' => 'Jersey futsal warna hitam',
            'is_published' => '1',
            'image' => UploadedFile::fake()->image('jersey-futsal.jpg'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('title', 'Jersey Tim Futsal')
            ->assertJsonPath('is_published', true);

        Storage::disk('public')->assertExists($response->json('path'));
        $this->assertDatabaseHas('gallery_items', [
            'title' => 'Jersey Tim Futsal',
            'is_published' => true,
        ]);

        GalleryItem::factory()->create(['is_published' => false]);

        $this->getJson('/api/site/gallery')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Jersey Tim Futsal');
    }

    public function test_gallery_image_requires_title_image_and_publication_status(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/gallery-items', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'image', 'is_published']);
    }

    public function test_gallery_management_requires_authentication(): void
    {
        $galleryItem = GalleryItem::factory()->create();

        $this->getJson('/api/gallery-items')->assertUnauthorized();
        $this->deleteJson("/api/gallery-items/{$galleryItem->id}")->assertUnauthorized();
    }
}
