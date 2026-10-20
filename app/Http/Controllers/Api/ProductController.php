<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category:id,name', 'thumbnail:id,product_id,path,alt_text,is_thumbnail,sort_order'])
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->integer('category_id'), fn ($query, int $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $images = Arr::pull($data, 'images', []);
        $product = Product::create($data);
        $product->update(['sku' => 'PRD-'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT)]);
        $this->storeImages($product, $images);

        return response()->json($product->fresh()->load($this->productRelations()), 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load($this->productRelations()));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $this->validatedData($request, $product);
        $images = Arr::pull($data, 'images', []);
        $product->update($data);
        $this->storeImages($product, $images);

        return response()->json($product->fresh()->load($this->productRelations()));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(status: 204);
    }

    public function destroyImage(Product $product, ProductImage $image): JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $wasThumbnail = $image->is_thumbnail;
        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($wasThumbnail) {
            $product->images()->oldest('sort_order')->first()?->update(['is_thumbnail' => true]);
        }

        return response()->json($product->fresh()->load($this->productRelations()));
    }

    public function setThumbnail(Product $product, ProductImage $image): JsonResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        $product->images()->update(['is_thumbnail' => false]);
        $image->update(['is_thumbnail' => true]);

        return response()->json($product->fresh()->load($this->productRelations()));
    }

    private function validatedData(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', Rule::unique('products', 'slug')->ignore($product)],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['active', 'draft'])],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $product);

        return $data;
    }

    private function storeImages(Product $product, array $images): void
    {
        $sortOrder = (int) $product->images()->max('sort_order');
        $hasThumbnail = $product->images()->where('is_thumbnail', true)->exists();

        foreach ($images as $image) {
            $sortOrder++;
            $path = $image->storeAs(
                "products/{$product->slug}",
                $this->imageFileName($product, $image),
                'public',
            );

            $product->images()->create([
                'path' => $path,
                'alt_text' => $product->name.' - Foto '.$sortOrder,
                'is_thumbnail' => ! $hasThumbnail,
                'sort_order' => $sortOrder,
            ]);
            $hasThumbnail = true;
        }
    }

    private function imageFileName(Product $product, UploadedFile $image): string
    {
        return $product->slug.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6)).'.'.$image->extension();
    }

    private function productRelations(): array
    {
        return ['category:id,name', 'images', 'thumbnail'];
    }

    private function uniqueSlug(string $value, ?Product $product = null): string
    {
        $baseSlug = Str::slug($value) ?: 'produk';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $slug)
                ->when($product, fn ($query) => $query->whereKeyNot($product->getKey()))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
