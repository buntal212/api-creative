<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryItemController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            GalleryItem::query()->orderBy('sort_order')->latest()->get(),
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request, true);
        $image = Arr::pull($data, 'image');
        $data['path'] = $this->storeImage($image);
        $data['sort_order'] ??= (int) GalleryItem::query()->max('sort_order') + 1;

        $galleryItem = GalleryItem::create($data);

        return response()->json($galleryItem, 201);
    }

    public function show(GalleryItem $galleryItem): JsonResponse
    {
        return response()->json($galleryItem);
    }

    public function update(Request $request, GalleryItem $galleryItem): JsonResponse
    {
        $data = $this->validatedData($request);
        $image = Arr::pull($data, 'image');

        if ($image instanceof UploadedFile) {
            $newPath = $this->storeImage($image);
            Storage::disk('public')->delete($galleryItem->path);
            $data['path'] = $newPath;
        }

        $galleryItem->update($data);

        return response()->json($galleryItem->fresh());
    }

    public function destroy(GalleryItem $galleryItem): JsonResponse
    {
        Storage::disk('public')->delete($galleryItem->path);
        $galleryItem->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request, bool $imageRequired = false): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_published' => ['required', 'boolean'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
    }

    private function storeImage(UploadedFile $image): string
    {
        return $image->storeAs(
            'gallery',
            now()->format('YmdHis').'-'.Str::lower(Str::random(8)).'.'.$image->extension(),
            'public',
        );
    }
}
