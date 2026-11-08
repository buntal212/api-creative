<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\SiteContact;
use Illuminate\Http\JsonResponse;

class PublicSiteContentController extends Controller
{
    public function contact(): JsonResponse
    {
        return response()->json(SiteContact::query()->first() ?? new SiteContact);
    }

    public function gallery(): JsonResponse
    {
        return response()->json(
            GalleryItem::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->latest()
                ->get(),
        );
    }
}
