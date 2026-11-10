<?php

namespace App\Models;

use Database\Factories\SiteContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'phone'])]
class SiteContact extends Model
{
    /** @use HasFactory<SiteContactFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $hidden = [
        'whatsapp',
        'email',
        'address',
        'instagram_url',
        'business_hours',
    ];
}
