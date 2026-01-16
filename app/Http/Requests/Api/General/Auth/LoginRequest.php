<?php

namespace App\Http\Requests\Api\General\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class LoginRequest extends FormRequest
{
    // use HandlesValidationErrorsTrait;

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
            'username' => 'required|string',
            'password' => 'required'
        ];
    }
}
