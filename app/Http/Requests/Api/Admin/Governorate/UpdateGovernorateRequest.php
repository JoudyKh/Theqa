<?php

namespace App\Http\Requests\Api\Admin\Governorate;

use Illuminate\Foundation\Http\FormRequest;
/**
 * @OA\Schema(
 *     schema="UpdateGovernorateRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", maxLength=255) 
 * )
 */
class UpdateGovernorateRequest extends FormRequest
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
            'name'  => ['string' , 'max:255'],
        ];
    }
}
