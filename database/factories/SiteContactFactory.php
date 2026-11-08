<?php

namespace Database\Factories;

use App\Models\SiteContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteContact>
 */
class SiteContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'whatsapp' => '6281234567890',
            'phone' => '0821-2345-6789',
            'email' => fake()->companyEmail(),
            'address' => fake()->address(),
            'instagram_url' => 'https://instagram.com/'.fake()->userName(),
            'business_hours' => 'Senin - Sabtu, 08.00 - 17.00 WIB',
        ];
    }
}
