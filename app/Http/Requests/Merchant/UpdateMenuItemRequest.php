<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMerchant() ?? false;
    }

    public function rules(): array
    {
        return [
            'group_name' => 'sometimes|string|max:100',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|integer|min:0',
            'photo' => 'nullable|image|max:2048',
            'is_available' => 'sometimes|boolean',
        ];
    }
}
