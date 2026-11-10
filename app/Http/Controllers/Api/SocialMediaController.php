<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SocialMediaController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SocialMedia::query()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $socialMedia = SocialMedia::query()->create($this->validatedData($request));

        return response()->json($socialMedia, 201);
    }

    public function update(Request $request, SocialMedia $socialMedium): JsonResponse
    {
        $socialMedium->update($this->validatedData($request));

        return response()->json($socialMedium->fresh());
    }

    public function destroy(SocialMedia $socialMedium): JsonResponse
    {
        $socialMedium->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url', 'max:255'],
        ]);
    }
}
