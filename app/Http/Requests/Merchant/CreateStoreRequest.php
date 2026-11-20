<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMerchant() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'address' => 'required|string|max:500',
            'phone' => 'nullable|string|max:20',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'price_level' => 'required|integer|between:1,3',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'opening_hours' => 'nullable|array',
            'opening_hours.*.day_of_week' => 'required_with:opening_hours|integer|between:0,6',
            'opening_hours.*.open_time' => 'nullable|string',
            'opening_hours.*.close_time' => 'nullable|string',
            'opening_hours.*.is_closed' => 'boolean',
            'cover' => 'nullable|image|max:2048',
        ];
    }
}
