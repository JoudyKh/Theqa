<?php

namespace App\Http\Requests\Api\Admin\Teacher;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Api\Admin\User\StoreUserRequest;

/**
 * @OA\Schema(
 *     schema="StoreTeacherRequest",
 *     type="object",
 *     required={"username", "email", "first_name", "last_name", "phone_number", "password", "description" , "image"},
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone_number", type="string", example="1234567890"),
 *     @OA\Property(property="password", type="string", example="password123"),
 *     @OA\Property(property="description", type="string", example="description"),
 *     @OA\Property(property="is_hidden", type="integer", example="1"),
 *     @OA\Property(property="image", type="string", format="binary")
 * )
 */
class StoreTeacherRequest extends StoreUserRequest
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
        return array_merge(
            parent::rules(),
            [
                'description' => 'required|string',
                'is_hidden' => 'nullable|boolean' ,
            ]
        );
    }
}
