<?php

namespace App\Http\Requests\ProductsRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateProductRequest extends FormRequest
{

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
        $rules = [
            'name_product' => 'sometimes|required|string|max:255',
            'slug' => $this->request->has('slug') ? 'required|nullable|string|max:255|unique:products,slug,' . $this->product->name_product : '',
            'description_product' => 'sometimes|nullable|string',
            'image_product' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:2048',
            'price' => 'sometimes|required|integer|min:0',
            'quantity' => 'sometimes|required|integer|min:0',
            'status_product' => 'sometimes|nullable|in:active,inactive',
        ];

        return $rules;
    }
}
