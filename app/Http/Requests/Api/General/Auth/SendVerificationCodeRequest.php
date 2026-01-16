<?php

namespace App\Http\Requests\Api\General\Auth;

use App\Models\AuthCode;
use App\Rules\UserRoleEmail;
use App\Rules\AdminRoleEmail;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class SendVerificationCodeRequest extends FormRequest
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
    public function rules(): array
    {
        $type = $this->get('type');

        $rules = [];

        if($type == AuthCode::UPDATE_FAMILY_PHONE_NUMBER){
            $rules = array_merge($rules, [
                'email' => ['prohibited'],
                'family_phone_number' => [
                    'required', 
                    'string', 
                    //Rule::unique('users' , 'family_phone_number')->whereNull('deleted_at'),
                ],
            ]);
        }elseif($type == AuthCode::FAMILY_PHONE_NUMBER_REGISTER){
            $rules = array_merge($rules, [
                'email' => ['prohibited'],
                'family_phone_number' => [
                    'required', 
                    'string', 
                    //Rule::unique('users' , 'family_phone_number')->whereNull('deleted_at'),
                ],
            ]);
        }elseif ($type == AuthCode::FORGET_PASSWORD) {
            $rules = array_merge($rules, [
                'family_phone_number' => ['prohibited'],
                'email' => ['required', 'email', (str_contains($this->url(), 'admin')) ? new AdminRoleEmail : new UserRoleEmail],
            ]);
        } elseif ($type == AuthCode::UPDATE_EMAIL) {
            $rules = array_merge($rules, [
                'family_phone_number' => ['prohibited'],
                'email' => ['nullable' , 'required', 'email', 'unique:users,email'] ,
            ]);
        } elseif ($type == AuthCode::VERIFY_EMAIL) {
            $rules = array_merge($rules, [
                'family_phone_number' => ['prohibited'],
                'email' => ['required', 'email', (str_contains($this->url(), 'admin')) ? new AdminRoleEmail(auth('sanctum')->id()) : new UserRoleEmail(auth('sanctum')->id())],
            ]);
        } elseif ($type == AuthCode::REGISTER) {
            $rules = array_merge($rules, [
                'family_phone_number' => ['prohibited'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]);
        } else {
            throw new \Exception('wrong type');
        }

        $rules = array_merge($rules, [
            'type' => ['required', 'string', Rule::in(AuthCode::ALL_TYPES)],
        ]);
        
        return $rules ;
    }
}