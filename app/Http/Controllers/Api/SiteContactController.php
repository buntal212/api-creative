<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteContactController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(SiteContact::query()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $contact = SiteContact::query()->create($this->validatedData($request));

        return response()->json($contact, 201);
    }

    public function update(Request $request, SiteContact $siteContact): JsonResponse
    {
        $siteContact->update($this->validatedData($request));

        return response()->json($siteContact->fresh());
    }

    public function destroy(SiteContact $siteContact): JsonResponse
    {
        $siteContact->delete();

        return response()->json(status: 204);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
        ]);
    }
}
