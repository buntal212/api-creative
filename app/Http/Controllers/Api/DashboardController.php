<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GalleryItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function summary(): JsonResponse
    {
        $activityStart = now()->startOfDay()->subDays(6);
        $productsByDate = Product::query()
            ->where('created_at', '>=', $activityStart)
            ->get(['id', 'created_at'])
            ->countBy(fn (Product $product): string => $product->created_at->toDateString());
        $productActivity = collect(range(0, 6))
            ->map(function (int $dayOffset) use ($activityStart, $productsByDate): array {
                $date = $activityStart->copy()->addDays($dayOffset);

                return [
                    'date' => $date->toDateString(),
                    'count' => $productsByDate->get($date->toDateString(), 0),
                ];
            })
            ->values();
        $recentActivities = Product::query()
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->latest('updated_at')
            ->limit(5)
            ->get(['id', 'name', 'created_at', 'updated_at'])
            ->map(fn (Product $product): array => $this->productActivity($product))
            ->concat(
                Category::query()
                    ->whereNotNull('created_at')
                    ->whereNotNull('updated_at')
                    ->latest('updated_at')
                    ->limit(5)
                    ->get(['id', 'name', 'created_at', 'updated_at'])
                    ->map(fn (Category $category): array => $this->categoryActivity($category)),
            )
            ->concat(
                GalleryItem::query()
                    ->whereNotNull('created_at')
                    ->whereNotNull('updated_at')
                    ->latest('updated_at')
                    ->limit(5)
                    ->get(['id', 'title', 'created_at', 'updated_at'])
                    ->map(fn (GalleryItem $galleryItem): array => $this->galleryActivity($galleryItem)),
            )
            ->concat(
                User::query()
                    ->whereNotNull('created_at')
                    ->whereNotNull('updated_at')
                    ->latest('updated_at')
                    ->limit(5)
                    ->get(['id', 'name', 'created_at', 'updated_at'])
                    ->map(fn (User $user): array => $this->userActivity($user)),
            )
            ->sortByDesc('occurred_at')
            ->take(3)
            ->values();

        return response()->json([
            'products' => Product::query()->count(),
            'categories' => Category::query()->count(),
            'gallery_items' => GalleryItem::query()->count(),
            'users' => User::query()->count(),
            'product_activity' => $productActivity,
            'recent_activities' => $recentActivities,
        ]);
    }

    /**
     * @return array{id: int, type: string, title: string, detail: string, occurred_at: string}
     */
    private function productActivity(Product $product): array
    {
        return [
            'id' => $product->id,
            'type' => 'product',
            'title' => $product->created_at->equalTo($product->updated_at)
                ? 'Produk ditambahkan'
                : 'Produk diperbarui',
            'detail' => $product->name,
            'occurred_at' => $product->updated_at->toIso8601String(),
        ];
    }

    /**
     * @return array{id: int, type: string, title: string, detail: string, occurred_at: string}
     */
    private function categoryActivity(Category $category): array
    {
        return [
            'id' => $category->id,
            'type' => 'category',
            'title' => $category->created_at->equalTo($category->updated_at)
                ? 'Kategori ditambahkan'
                : 'Kategori diperbarui',
            'detail' => $category->name,
            'occurred_at' => $category->updated_at->toIso8601String(),
        ];
    }

    /**
     * @return array{id: int, type: string, title: string, detail: string, occurred_at: string}
     */
    private function galleryActivity(GalleryItem $galleryItem): array
    {
        return [
            'id' => $galleryItem->id,
            'type' => 'gallery',
            'title' => $galleryItem->created_at->equalTo($galleryItem->updated_at)
                ? 'Foto galeri ditambahkan'
                : 'Foto galeri diperbarui',
            'detail' => $galleryItem->title,
            'occurred_at' => $galleryItem->updated_at->toIso8601String(),
        ];
    }

    /**
     * @return array{id: int, type: string, title: string, detail: string, occurred_at: string}
     */
    private function userActivity(User $user): array
    {
        return [
            'id' => $user->id,
            'type' => 'user',
            'title' => $user->created_at->equalTo($user->updated_at)
                ? 'Pengguna ditambahkan'
                : 'Pengguna diperbarui',
            'detail' => $user->name,
            'occurred_at' => $user->updated_at->toIso8601String(),
        ];
    }
}
