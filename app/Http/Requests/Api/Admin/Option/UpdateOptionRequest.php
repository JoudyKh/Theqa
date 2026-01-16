<?php

namespace App\Http\Requests\Api\Admin\Option;

use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     title="UpdateOptionRequest",
 *     description="Request body for updating an existing option",
 *     type="object",
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The text or label of the option",
 *         example="Updated option text"
 *     ),
 *     @OA\Property(
 *         property="is_true",
 *         type="boolean",
 *         description="Indicates whether this option is the correct answer",
 *         example=false
 *     )
 * )
 */
class UpdateOptionRequest extends FormRequest
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
            'name' => ['string'],
            'is_true' => ['boolean'],
        ];
    }
}
