<?php

namespace App\Http\Requests\Api\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="StoreCouponRequest",
 *     type="object",
 *     required={"coupon", "discount_percentage", "expires_at"},
 *     @OA\Property(property="coupon", type="string", maxLength=255),
 *     @OA\Property(property="discount_percentage", type="integer", minimum=0, maximum=100),
 *     @OA\Property(property="expires_at", type="string", format="date-time"),
 *     @OA\Property(property="usage_limit", type="integer", minimum=1, description="Maximum number of times the coupon can be used"),
 * )
 */
class StoreCouponRequest extends FormRequest
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
            'coupon' => ['required', 'string', 'max:255', Rule::unique('coupons')->ignore($this->coupon)],
            'discount_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'expires_at' => ['required', 'date', 'after:today'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
