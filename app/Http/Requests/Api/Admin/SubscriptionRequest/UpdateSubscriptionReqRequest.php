<?php

namespace App\Http\Requests\Api\Admin\SubscriptionRequest;

use App\Models\StudentExam;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use App\Enums\SectionStudentStatusEnum;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="UpdateSubscriptionReqRequest",
 *     type="object",
 *     @OA\Property(
 *        property="status",
 *        type="string",
 *        enum={"pending","rejected","accepted"},
 *     ),
 *     @OA\Property(
 *        property="refuse_reason",
 *        type="string",
 *        example="the image is not clear ",
 *     ),
 * )
 */
class UpdateSubscriptionReqRequest extends FormRequest
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
            'status' => 'in:' . implode(',', SectionStudentStatusEnum::all()),
            'reject_reason' => 'required_if:status,'.SectionStudentStatusEnum::REJECTED->value
        ];
    }
}
