<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\SocialMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SiteContentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_manage_site_contacts(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $contact = $this->postJson('/api/site-contacts', [
            'name' => 'Admin Creative',
            'phone' => '0823-3414-8314',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Admin Creative');

        $this->assertDatabaseHas('site_contacts', [
            'name' => 'Admin Creative',
            'phone' => '0823-3414-8314',
        ]);

        $this->putJson('/api/site-contacts/'.$contact->json('id'), [
            'name' => 'Admin Penjualan',
            'phone' => '0812-3456-7890',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Admin Penjualan');

        $this->getJson('/api/site/contact')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.phone', '0812-3456-7890');

        $this->deleteJson('/api/site-contacts/'.$contact->json('id'))
            ->assertNoContent();
    }

    public function test_site_contact_update_requires_authentication(): void
    {
        $this->postJson('/api/site-contacts', [
            'name' => 'Admin Creative',
            'phone' => '0823-3414-8314',
        ])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_manage_social_media(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $socialMedia = $this->postJson('/api/social-media', [
            'name' => 'Instagram',
            'url' => 'https://instagram.com/creative',
        ])
            ->assertCreated()
            ->assertJsonPath('name', 'Instagram');

        $this->putJson('/api/social-media/'.$socialMedia->json('id'), [
            'name' => 'TikTok',
            'url' => 'https://tiktok.com/@creative',
        ])
            ->assertOk()
            ->assertJsonPath('url', 'https://tiktok.com/@creative');

        $this->getJson('/api/site/social-media')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.name', 'TikTok');

        $this->deleteJson('/api/social-media/'.$socialMedia->json('id'))
            ->assertNoContent();
    }

    public function test_social_media_management_requires_authentication(): void
    {
        SocialMedia::factory()->create();

        $this->getJson('/api/social-media')->assertUnauthorized();
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
