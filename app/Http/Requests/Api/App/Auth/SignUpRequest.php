<?php

namespace App\Http\Requests\Api\App\Auth;

use App\Constants\TheqaInfo;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class SignUpRequest extends FormRequest
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
        
        $rules = TheqaInfo::getValidationRules()['signup'];

        $rules = array_merge($rules , [
            'verification_code' => [Rule::requiredIf($this->get('email') !== null)],
        ]) ;

        return $rules ;
    }
}