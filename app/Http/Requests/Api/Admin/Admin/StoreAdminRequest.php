<?php

namespace App\Http\Requests\Api\Admin\Admin;

use App\Http\Requests\Api\Admin\User\StoreUserRequest;

/**
 * @OA\Schema(
 *     schema="StoreAdminRequest",
 *     type="object",
 *     required={"username", "email", "first_name", "last_name", "phone_number", "password", "image"},
 *     @OA\Property(property="username", type="string", example="johndoe"),
 *     @OA\Property(property="email", type="string", example="johndoe@example.com"),
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone_number", type="string", example="1234567890"),
 *     @OA\Property(property="password", type="string", example="password123"),
 *     @OA\Property(property="image", type="string", format="binary")
 * )
 */
class StoreAdminRequest extends StoreUserRequest
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
        return parent::rules();
    }
}
