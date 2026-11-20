<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMerchant() ?? false;
    }

    public function rules(): array
    {
        return [
            'group_name' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|integer|min:0',
            'photo' => 'nullable|image|max:2048',
            'is_available' => 'boolean',
        ];
    }
}
