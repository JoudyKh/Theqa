<?php

namespace App\Http\Requests\Api\Admin\PurchaseCode;

use App\Constants\Constants;
use App\Models\PurchaseCode;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdatePurchaseCodeRequest",
 *     type="object",
 *     required={},
 *     @OA\Property(property="code", type="string", example="ABCD1234", minLength=4, maxLength=16),
 *     @OA\Property(property="expire_date", type="string", format="date", example="2024-12-31"),
 *     @OA\Property(property="count", type="integer", example=10, minimum=0, maximum=1000),
 *     @OA\Property(
 *         property="courses",
 *         type="array",
 *         @OA\Items(type="integer", example=1)
 *     ),
 *     @OA\Property(
 *         property="trashed_courses",
 *         type="array",
 *         @OA\Items(type="integer", example=1)
 *     )
 * )
 */
class UpdatePurchaseCodeRequest extends FormRequest
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
        $purchaseCode = $this->route('purchaseCode');

        return [
            'code' => ['string' , 'min:4' , 'max:16' , Rule::unique('purchase_codes' , 'code')->ignore($purchaseCode->id)],
            'expire_date' => ['date'],
            'usage_limit' => ['integer' , 'min:0' , 'max:1000'],
            'count' => ['integer' , 'min:0' , 'max:1000'],
            'courses' => ['array'],
            'courses.*' => [
                'required' ,
                'integer',
                'distinct' ,
                Rule::exists('sections' , 'id')
                    ->whereNull('deleted_at')
                    ->where('type' , Constants::SECTION_TYPE_COURSES) ,
            ],
        ];
    }
}
