<?php

namespace App\Http\Controllers\Api\Merchant;

use App\Http\Requests\Merchant\CreateStoreRequest;
use App\Http\Requests\Merchant\UpdateStoreRequest;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreController
{
    public function show(Request $request)
    {
        $user = $request->user();
        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        $restaurant->load(['categories', 'openingHours']);

        return ApiResponse::success($this->formatStore($restaurant));
    }

    public function store(CreateStoreRequest $request)
    {
        $user = $request->user();

        if ($user->restaurant()->exists()) {
            return ApiResponse::error(
                message: 'Anda sudah memiliki toko.',
                code: 'store_already_exists',
                status: 422
            );
        }

        return DB::transaction(function () use ($request, $user) {
            $data = $request->safe()->except(['categories', 'opening_hours', 'cover']);
            $data['owner_id'] = $user->id;
            $data['status'] = 'pending';
            $data['timezone'] = 'Asia/Jakarta';

            if ($request->hasFile('cover')) {
                $data['cover_path'] = $request->file('cover')->store('covers', 'public');
            }

            $restaurant = Restaurant::create($data);

            if ($request->filled('categories')) {
                $restaurant->categories()->sync($request->input('categories'));
            }

            if ($request->filled('opening_hours')) {
                foreach ($request->input('opening_hours') as $h) {
                    $restaurant->openingHours()->create([
                        'day_of_week' => $h['day_of_week'],
                        'open_time' => $h['open_time'] ?? null,
                        'close_time' => $h['close_time'] ?? null,
                        'is_closed' => (bool) ($h['is_closed'] ?? false),
                    ]);
                }
            }

            $restaurant->load(['categories', 'openingHours']);

            return ApiResponse::success($this->formatStore($restaurant), 201);
        });
    }

    public function update(UpdateStoreRequest $request)
    {
        $user = $request->user();
        $restaurant = $user->restaurant;

        if (! $restaurant) {
            return ApiResponse::error(
                message: 'Toko belum dibuat.',
                code: 'store_not_found',
                status: 404
            );
        }

        return DB::transaction(function () use ($request, $restaurant) {
            $data = $request->safe()->except(['categories', 'opening_hours', 'cover']);

            if ($request->hasFile('cover')) {
                if ($restaurant->cover_path) {
                    Storage::disk('public')->delete($restaurant->cover_path);
                }
                $data['cover_path'] = $request->file('cover')->store('covers', 'public');
            }

            $restaurant->update($data);

            if ($request->has('categories')) {
                $restaurant->categories()->sync($request->input('categories'));
            }

            if ($request->has('opening_hours')) {
                $restaurant->openingHours()->delete();
                foreach ($request->input('opening_hours') as $h) {
                    $restaurant->openingHours()->create([
                        'day_of_week' => $h['day_of_week'],
                        'open_time' => $h['open_time'] ?? null,
                        'close_time' => $h['close_time'] ?? null,
                        'is_closed' => (bool) ($h['is_closed'] ?? false),
                    ]);
                }
            }

            $restaurant->load(['categories', 'openingHours']);

            return ApiResponse::success($this->formatStore($restaurant));
        });
    }

    private function formatStore(Restaurant $restaurant): array
    {
        $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        return [
            'id' => $restaurant->id,
            'name' => $restaurant->name,
            'description' => $restaurant->description,
            'address' => $restaurant->address,
            'phone' => $restaurant->phone,
            'lat' => $restaurant->lat,
            'lng' => $restaurant->lng,
            'price_level' => $restaurant->price_level,
            'status' => $restaurant->status,
            'status_reason' => $restaurant->status_reason,
            'cover_url' => $restaurant->cover_path
                ? Storage::disk('public')->url($restaurant->cover_path)
                : null,
            'categories' => $restaurant->categories->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ]),
            'opening_hours' => $restaurant->openingHours
                ->sortBy('day_of_week')
                ->values()
                ->map(fn ($h) => [
                    'day_of_week' => $h->day_of_week,
                    'day' => $dayLabels[$h->day_of_week] ?? $h->day_of_week,
                    'open' => $h->open_time ? substr($h->open_time, 0, 5) : null,
                    'close' => $h->close_time ? substr($h->close_time, 0, 5) : null,
                    'is_closed' => $h->is_closed,
                ]),
        ];
    }
}
