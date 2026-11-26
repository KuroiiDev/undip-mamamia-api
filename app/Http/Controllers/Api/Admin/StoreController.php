<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Restaurant::with('owner:id,name,email');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($q) {
                        $ownerQuery->where('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    });
            });
        }

        $paginator = $query->latest('id')->cursorPaginate(15);

        $items = collect($paginator->items())->map(fn (Restaurant $r) => [
            'id' => $r->id,
            'name' => $r->name,
            'status' => $r->status,
            'status_reason' => $r->status_reason,
            'address' => $r->address,
            'phone' => $r->phone,
            'rating_avg' => (float) $r->rating_avg,
            'rating_count' => (int) $r->rating_count,
            'cover_url' => ApiResponse::storageUrl($r->cover_path),
            'owner' => $r->owner ? [
                'id' => $r->owner->id,
                'name' => $r->owner->name,
                'email' => $r->owner->email,
            ] : null,
            'created_at' => $r->created_at?->toISOString(),
        ])->values()->all();

        return ApiResponse::paginated(
            data: $items,
            nextCursor: $paginator->nextCursor()?->encode()
        );
    }

    public function approve(string $id): JsonResponse
    {
        $restaurant = Restaurant::findOrFail($id);

        $restaurant->update([
            'status' => 'active',
            'status_reason' => null,
        ]);

        return ApiResponse::success([
            'message' => 'Toko berhasil disetujui.',
            'store' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'status' => $restaurant->status,
            ],
        ]);
    }

    public function ban(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $restaurant = Restaurant::findOrFail($id);

        $restaurant->update([
            'status' => 'banned',
            'status_reason' => $validated['reason'],
        ]);

        return ApiResponse::success([
            'message' => 'Toko berhasil diblokir.',
            'store' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'status' => $restaurant->status,
                'status_reason' => $restaurant->status_reason,
            ],
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $restaurant = Restaurant::findOrFail($id);

        $restaurant->update([
            'status_reason' => $validated['reason'],
        ]);

        $restaurant->delete();

        return ApiResponse::success([
            'message' => 'Toko berhasil dihapus.',
        ]);
    }
}
