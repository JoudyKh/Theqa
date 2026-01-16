<?php

namespace App\Http\Requests\Api\Admin\PurchaseCode;

use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StorePurchaseCodeRequest",
 *     type="object",
 *     required={"code", "expire_date", "usage_limit", "courses"},
 *     @OA\Property(property="code", type="string", example="ABCD1234", minLength=4, maxLength=16),
 *     @OA\Property(property="expire_date", type="string", format="date", example="2024-12-31"),
 *     @OA\Property(property="usage_limit", type="integer", example=10, minimum=0, maximum=1000),
 *     @OA\Property(
 *         property="courses",
 *         type="array",
 *         @OA\Items(type="integer", example=1)
 *     )
 * )
 */
class StorePurchaseCodeRequest extends FormRequest
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
            'code' => ['required' , 'string' , 'min:4' , 'max:16' , 'unique:purchase_codes,code'],
            'expire_date' => ['required' , 'date'],
            'usage_limit' => ['required' , 'integer' , 'min:0' , 'max:1000'],
            'courses' => ['required' , 'array'],
            'courses.*' => ['required' , 'integer' , 'distinct' , Rule::exists('sections' , 'id')->where('type' , Constants::SECTION_TYPE_COURSES)],
        ];
    }
}
