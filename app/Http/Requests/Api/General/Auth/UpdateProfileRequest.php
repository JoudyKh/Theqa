<?php

namespace App\Http\Requests\Api\General\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\HandlesValidationErrorsTrait;

class UpdateProfileRequest extends FormRequest
{
    // use HandlesValidationErrorsTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'is_active' => ['prohibited'],
            'city_id' => ['integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],

            'phone_number_country_code' => ['string', 'min:1', 'max:255'],
            'family_phone_number_country_code' => ['string', 'min:1', 'max:255'],

            'phone_number' => 'string|max:255|min:8',
            'family_phone_number' => ['string', 'max:255', 'min:8'],

            'username' => [
                'string',
                'max:255',
                Rule::unique('users', 'username')
                    ->whereNull('deleted_at')
                    ->ignore(request()->user()->id)
            ],

            'email' => [
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore(request()->user()->id)
            ],

            'full_name' => 'string|max:255',
            'location' => 'nullable|string|max:255',
            'birth_date' => 'nullable|string|date_format:Y-m-d',

            'first_name' => 'string|max:255|min:2',
            'last_name' => 'string|max:255|min:2',
            'password' => 'nullable|min:8|confirmed',
            'old_password' => 'required_with:password',
            'image' => 'nullable|image|mimes:png,jpg,jpeg',
            'trash_images_ids' => ['array'],
            'trash_images_ids.*' => ['exists:user_images,id,user_id,' . request()->user()->id],
        ];
    }
}
