<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            //
                 'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    
    public function messages(): array
    {
        return [
            'items.required' => 'Items are required',
            'items.array' => 'Items must be an array',
            'items.min' => 'At least one item is required',

            'items.*.product_id.required' => 'Product ID is required',
            'items.*.product_id.exists' => 'Product not found',

            'items.*.quantity.required' => 'Quantity is required',
            'items.*.quantity.min' => 'Quantity must be at least 1',
        ];
    }
}
