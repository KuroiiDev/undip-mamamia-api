<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Storage;

class MenuController
{
    public function __invoke(string $id)
    {
        $restaurant = Restaurant::active()->findOrFail($id);

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
                            ? Storage::disk('public')->url($item->photo_path)
                            : null,
                        'is_available' => $item->is_available,
                    ]),
                ];
            })
            ->values();

        return ApiResponse::success($menus);
    }
}
