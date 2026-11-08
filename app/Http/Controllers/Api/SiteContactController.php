<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteContactController extends Controller
{
    public function show(): JsonResponse
    {
        return response()->json(SiteContact::query()->first() ?? new SiteContact);
    }

    public function update(Request $request): JsonResponse
    {
        $contact = SiteContact::query()->firstOrNew();
        $contact->fill($this->validatedData($request));
        $contact->save();

        return response()->json($contact);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'business_hours' => ['nullable', 'string', 'max:160'],
        ]);
    }
}
