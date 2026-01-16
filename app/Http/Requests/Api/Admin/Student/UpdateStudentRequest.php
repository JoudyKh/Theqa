<?php

namespace App\Http\Requests\Api\Admin\Student;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\Api\Admin\User\UpdateUserRequest;

/**
 * @OA\Schema(
 *     schema="UpdateStudentRequest",
 *     type="object",
 *     @OA\Property(property="phone_number_country_code", type="string", example="1234567890"),
 *     @OA\Property(property="family_phone_number_country_code", type="string", example="123456789"),
 *     @OA\Property(property="password", type="string"),
 *     @OA\Property(property="password_confirmation", type="string"),
 *     @OA\Property(property="is_banned", type="integer" , enum={1,0}),
 *     @OA\Property(property="is_active", type="integer" , enum={1,0}),
 *     @OA\Property(property="city_id", type="integer"),
 *     @OA\Property(property="family_phone_number", type="string", example="1234567890"),
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *     @OA\Property(property="full_name", type="string", example="John d d"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone_number", type="string", example="1234567890"),
 *     @OA\Property(property="image", type="string", format="binary"),
 *     @OA\Property(property="description", type="string", example="Doe description"),
 *     @OA\Property(property="location", type="string", example="syria"),
 *     @OA\Property(property="birth_date", type="string", example="2000-02-02"),
 * )
 */
class UpdateStudentRequest extends UpdateUserRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $parentRules = parent::rules();

        $studentRules = [

            'phone_number_country_code' => ['string', 'min:1', 'max:255'],
            'family_phone_number_country_code' => ['string', 'min:1', 'max:255'],

            'is_active' => ['boolean'],
            'is_banned' => ['boolean'],

            'location' => ['string', 'max:255'],
            'birth_date' => ['string', 'date_format:Y-m-d'],
            'city_id' => ['integer', Rule::exists('cities', 'id')->whereNull('deleted_at')],
            'family_phone_number' => ['string', 'max:255'],
            'description' => ['string', 'max:40000'],
        ];

        return array_merge($parentRules, $studentRules);
    }
}
