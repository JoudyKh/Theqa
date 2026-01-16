<?php

namespace App\Http\Requests\Api\App\SubscriptionRequest;

use Carbon\Carbon;
use App\Models\Coupon;
use App\Models\Section;
use App\Models\StudentExam;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="CreateSubscriptionReqRequest",
 *     type="object",
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="binary",
 *         description="The image file of the student"
 *     ),
 *     @OA\Property(
 *         property="section_id",
 *         type="integer",
 *         description="The ID of the section."
 *     )
 * )
 */
class CreateSubscriptionReqRequest extends FormRequest
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
                function ($attribute, $value, $fail) {
                    $course = Section::where([
                        'id' => $value,
                        'type' => Constants::SECTION_TYPE_COURSES
                    ])->first();

                    if (!$course) {
                        $fail("course with id : $value does not exists");
                        return;
                    }


                    $firstUnit = $course->subSections()->first();
                    if (!$firstUnit) {
                        $fail(__('messages.section_has_no_sub_sections'));
                        return;
                    }
                    $firstLesson = $firstUnit->lessons()->first();


                    if (!$firstLesson) {
                        $fail(__('messages.section_has_no_lessons'));
                        return;
                    }
                }
            ],
            'image' => [
                function ($attribute, $value, $fail) {
                    if (
                        $this->missing('image')
                        and
                        (
                            Section::where('id', $this->get('section_id'))->first()?->discount == 100
                            or
                            Coupon::where('coupon', $this->get('coupon'))->first()?->discount_percentage == 100
                        )
                    ) {
                        $fail(__('messages.image_required'));
                        return;
                    }
                },
                'file',
                'mimes:jpeg,png,jpg,gif',
                'max:4096'
            ],
            'coupon' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $coupon = Coupon::where('coupon', $value)->first();

                    if (!$coupon) {
                        $fail(__('messages.coupon_does_not_exists'));
                        return;
                    }

                    if ($coupon->expires_at !== null and Carbon::parse($coupon->expires_at)->isPast()) {
                        $fail(__('messages.coupon_is_expired'));
                        return;
                    }

                    if ($coupon->usage_limit !== null and !$coupon->usage_limit) {
                        $fail(__('messages.coupon_limit_is_done'));
                        return;
                    }

                    $this->mergeIfMissing(['coupon_id' => $coupon->id]);
                }
            ],
        ];
    }
}
