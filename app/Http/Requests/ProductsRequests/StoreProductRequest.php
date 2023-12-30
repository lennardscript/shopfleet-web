<?php

namespace App\Http\Requests\ProductsRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name_product' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:products',
            'description_product' => 'nullable|string',
            'image_product' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'required|integer|min:0',
            'quantity' => 'required|integer|min:0',
            'status_product' => 'nullable|in:active,inactive',
            'category' => 'nullable|string'
        ];
    }
}
