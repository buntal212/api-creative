<?php

namespace Database\Seeders;

use App\Models\SiteContact;
use Illuminate\Database\Seeder;

class SiteContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteContact::query()->firstOrCreate([], SiteContact::factory()->make()->toArray());
    }
}
