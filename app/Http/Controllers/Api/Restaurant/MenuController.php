<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class MenuController
{
    public function __invoke(Request $request, string $id)
    {
        $query = $request->user()?->isAdmin() ? Restaurant::query() : Restaurant::active();
        $restaurant = $query->findOrFail($id);

        $menus = $restaurant->menuItems()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name')
            ->map(function ($items, $group) {
                return [
                    'group' => $group,
                    'items' => $items->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'price_label' => 'Rp '.number_format($item->price, 0, ',', '.'),
                        'description' => $item->description,
                        'photo_url' => $item->photo_path
                            ? ApiResponse::storageUrl($item->photo_path)
                            : null,
                        'is_available' => $item->is_available,
                    ]),
                ];
            })
            ->values();

        return ApiResponse::success($menus);
    }
}
