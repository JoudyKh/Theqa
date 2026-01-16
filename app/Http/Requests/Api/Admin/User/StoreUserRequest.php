<?php

namespace App\Http\Requests\Api\Admin\User;

use Illuminate\Validation\Rule;
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
            'username' => [
                        'required',
                        'string',
                        'max:255',
                        Rule::unique('users', 'username')->whereNull('deleted_at'),
                    ],
            'email' => [
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'full_name' => ['string' , 'max:255'],
            'first_name' => ['string' , 'max:255'],
            'last_name' => ['string' , 'max:255'],
            'phone_number' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'image' => 'image|mimes:png,jpg,jpeg'
        ];
    }
}