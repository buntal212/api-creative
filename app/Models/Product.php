<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

#[Fillable(['category_id', 'name', 'slug', 'sku', 'description', 'price', 'stock', 'status'])]
class Product extends Model
{
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function thumbnail(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_thumbnail', true);
    }

    protected static function booted(): void
    {
        static::deleting(function (Product $product): void {
            $product->images()->each(function (ProductImage $image): void {
                Storage::disk('public')->delete($image->path);
            });
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }
}
