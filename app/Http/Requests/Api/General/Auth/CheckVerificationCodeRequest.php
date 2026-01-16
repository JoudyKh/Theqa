<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Models\AuthCode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class CheckVerificationCodeRequest extends FormRequest
{
    // use HandlesValidationErrorsTrait;

    public function prepareForValidation()
    {
        if ($this->missing('type')) {
            $this->merge(['type' => AuthCode::FORGET_PASSWORD]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return !in_array($this->get('type'), AuthCode::REQUIRED_AUTH) or auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $user = auth('sanctum')->user();

        $rules = array_merge([
            'verification_code' => 'required',

        ], (!$user ? [
                'email' => [
                    'string',
                    'email',
                    Rule::requiredIf(
                        in_array($this->get('type'), [
                            AuthCode::REGISTER,
                            AuthCode::FORGET_PASSWORD,
                            AuthCode::UPDATE_EMAIL,
                            AuthCode::VERIFY_EMAIL,
                        ])
                    )
                ],
                'family_phone_number' => [
                    'string',
                    Rule::requiredIf(
                        in_array($this->get('type'), [
                            AuthCode::FAMILY_PHONE_NUMBER_REGISTER,
                        ])
                    )
                ],
            ] : []));

        return array_merge($rules, [
            'type' => [
                'required',
                'string',
                Rule::in(AuthCode::ALL_TYPES) 
            ],
        ]);
    }
}
