<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar_url' => $this->user->avatar_path
                    ? Storage::disk('public')->url($this->user->avatar_path)
                    : null,
            ],
            'created_label' => $this->created_at->diffForHumans(),
            'is_mine' => $request->user()?->id === $this->user_id,
        ];
    }
}
