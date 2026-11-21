<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController
{
    public function __invoke()
    {
        $categories = Cache::remember('categories', 3600, function () {
            return Category::select('id', 'name', 'icon_path')
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'icon_url' => $c->icon_path
                        ? ApiResponse::storageUrl($c->icon_path)
                        : null,
                ])
                ->values()
                ->all();
        });

        return ApiResponse::success($categories);
    }
}
