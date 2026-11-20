<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpdateProfileController
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'avatar' => 'sometimes|image|max:2048',
        ]);

        $user = $request->user();

        if ($request->filled('name')) {
            $user->name = $request->input('name');
        }

        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->save();

        return ApiResponse::success(new UserResource($user));
    }
}
