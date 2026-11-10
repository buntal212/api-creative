<?php

namespace App\Models;

use Database\Factories\SocialMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'url'])]
class SocialMedia extends Model
{
    /** @use HasFactory<SocialMediaFactory> */
    use HasFactory;
}
