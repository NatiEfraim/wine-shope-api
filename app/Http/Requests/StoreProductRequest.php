<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (empty($this->sku)) {
            $this->merge([
                'sku' => strtoupper(Str::random(8)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quantity' => ['required', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'Product SKU is required',
            'sku.unique' => 'Product SKU already exists',
            'name.required' => 'Product name is required',
            'price.required' => 'Product price is required',
            'quantity.required' => 'Product quantity is required',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Image must be jpg, jpeg, png, or webp',
            'image.max' => 'Image size must not be bigger than 2MB',
        ];
    }
}