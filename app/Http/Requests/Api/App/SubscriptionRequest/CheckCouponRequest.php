<?php

namespace App\Http\Requests\Api\App\SubscriptionRequest;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CheckCouponRequest extends FormRequest
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
            'section_id' => [
                'required',
                'integer',
                Rule::exists('sections', 'id')->where('type', Constants::SECTION_TYPE_COURSES)
            ],
            'coupon' => ['required', 'string', 'max:255'],
        ];
    }
}
