<?php

namespace App\Models;

use Database\Factories\SiteContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['whatsapp', 'phone', 'email', 'address', 'instagram_url', 'business_hours'])]
class SiteContact extends Model
{
    /** @use HasFactory<SiteContactFactory> */
    use HasFactory;
}
