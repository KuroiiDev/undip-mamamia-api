<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('q')) {
            $q = $request->query('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $paginator = $query->latest('id')->cursorPaginate(15);

        $items = collect($paginator->items())->map(fn (User $u) => [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'role' => $u->role,
            'status' => $u->status,
            'banned_reason' => $u->banned_reason,
            'avatar_url' => ApiResponse::storageUrl($u->avatar_path),
            'created_at' => $u->created_at?->toISOString(),
        ])->values()->all();

        return ApiResponse::paginated(
            data: $items,
            nextCursor: $paginator->nextCursor()?->encode()
        );
    }

    public function ban(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $user = User::findOrFail($id);

        if ($user->isAdmin()) {
            return ApiResponse::error(
                message: 'Tidak dapat memblokir akun admin.',
                status: 403
            );
        }

        $user->update([
            'status' => 'banned',
            'banned_reason' => $validated['reason'],
        ]);

        // Revoke all Sanctum tokens immediately
        $user->tokens()->delete();

        return ApiResponse::success([
            'message' => 'User berhasil diblokir.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
                'banned_reason' => $user->banned_reason,
            ],
        ]);
    }

    public function unban(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'active',
            'banned_reason' => null,
        ]);

        return ApiResponse::success([
            'message' => 'User berhasil diaktifkan kembali.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'status' => $user->status,
            ],
        ]);
    }
}
