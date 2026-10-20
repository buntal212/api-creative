<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'path', 'alt_text', 'is_thumbnail', 'sort_order'])]
class ProductImage extends Model
{
    protected $appends = ['url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('public')->url($this->path));
    }

    protected function casts(): array
    {
        return ['is_thumbnail' => 'boolean'];
    }
}
