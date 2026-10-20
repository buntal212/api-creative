<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Category::query()->withCount('products')->latest()->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $category = Category::create($this->validatedData($request));

        return response()->json($category->loadCount('products'), 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $category->update($this->validatedData($request, $category));

        return response()->json($category->fresh()->loadCount('products'));
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('categories', 'slug')->ignore($category),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $category);

        return $data;
    }

    private function uniqueSlug(string $value, ?Category $category = null): string
    {
        $baseSlug = Str::slug($value) ?: 'kategori';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Category::query()
                ->where('slug', $slug)
                ->when($category, fn ($query) => $query->whereKeyNot($category->getKey()))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        return $slug;
    }
}
