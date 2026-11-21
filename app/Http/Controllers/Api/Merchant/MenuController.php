<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Requests\Merchant\CreateMenuItemRequest;
use App\Http\Requests\Merchant\UpdateMenuItemRequest;
use App\Models\MenuItem;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController
{
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $items = $restaurant->menuItems()
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => $this->formatMenuItem($item));

        return ApiResponse::success($items);
    }

    public function store(CreateMenuItemRequest $request)
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $data = $request->safe()->except(['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('menus', 'public');
        }

        $menuItem = $restaurant->menuItems()->create($data);

        return ApiResponse::success($this->formatMenuItem($menuItem), 201);
    }

    public function update(UpdateMenuItemRequest $request, string $id)
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $menuItem = $restaurant->menuItems()->findOrFail($id);

        $data = $request->safe()->except(['photo']);

        if ($request->hasFile('photo')) {
            if ($menuItem->photo_path) {
                Storage::disk('public')->delete($menuItem->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('menus', 'public');
        }

        $menuItem->update($data);

        return ApiResponse::success($this->formatMenuItem($menuItem));
    }

    public function destroy(Request $request, string $id)
    {
        $restaurant = $request->user()->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $menuItem = $restaurant->menuItems()->findOrFail($id);
        $menuItem->delete();

        return response()->noContent();
    }

    private function formatMenuItem(MenuItem $item): array
    {
        return [
            'id' => $item->id,
            'group_name' => $item->group_name,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->price,
            'price_label' => 'Rp '.number_format($item->price, 0, ',', '.'),
            'photo_url' => $item->photo_path
                ? ApiResponse::storageUrl($item->photo_path)
                : null,
            'is_available' => $item->is_available,
        ];
    }
}
