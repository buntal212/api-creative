<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->whereHas('products', fn ($query) => $query->where('status', 'active'))
            ->withCount([
                'products as products_count' => fn ($query) => $query->where('status', 'active'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']);

        $thumbnails = Product::query()
            ->where('status', 'active')
            ->whereIn('category_id', $categories->pluck('id'))
            ->with('thumbnail:id,product_id,path,alt_text,is_thumbnail,sort_order')
            ->latest()
            ->get(['id', 'category_id', 'created_at'])
            ->groupBy('category_id')
            ->map(fn ($products) => $products->first()?->thumbnail);

        return response()->json($categories->map(fn (Category $category) => [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'products_count' => $category->products_count,
            'thumbnail' => $thumbnails->get($category->id),
        ]));
    }

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->where('status', 'active')
            ->with(['category:id,name,slug', 'thumbnail:id,product_id,path,alt_text,is_thumbnail,sort_order'])
            ->when($request->string('search')->trim()->value(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        abort_unless($product->status === 'active', 404);

        return response()->json($product->load([
            'category:id,name,slug',
            'images',
            'thumbnail',
        ]));
    }
}
