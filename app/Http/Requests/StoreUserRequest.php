<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'personal_id' => ['required', 'string', 'max:50', 'unique:users,personal_id'],
            'phone' => ['required', 'string', 'max:20'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],

        ];
    }

        public function messages(): array
    {
        return [
            'email.unique' => 'Email already exists',
            'personal_id.unique' => 'Personal ID already exists',
        ];
    }

}
