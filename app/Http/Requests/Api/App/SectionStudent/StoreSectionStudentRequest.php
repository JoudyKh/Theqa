<?php

namespace App\Http\Requests\Api\App\SectionStudent;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Section;
use App\Models\PurchaseCode;
use App\Models\SectionStudent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreSectionStudentRequest",
 *     type="object",
 *                 required={"course_id", "purchase_code"},
 *                 @OA\Property(
 *                     property="section_id",
 *                     type="integer",
 *                     description="The ID of the course"
 *                 ),
 *                 @OA\Property(
 *                     property="purchase_code",
 *                     type="string",
 *                     description="The purchase code for the course"
 *                 )
 * )
 */
class StoreSectionStudentRequest extends FormRequest
{
    private $section = null ;
    private $courseIsFree = false ;

    public function prepareForValidation()
    {
        $this->section = Section::where('id', $this->get('section_id'))->first();
        
        if($this->section->is_free)
        {
            $this->courseIsFree = true ;
        }else{
            $this->courseIsFree = (0 == ($this->section?->price - ($this->section?->price * ($this->section->discount / 100)))) ;
        }

        $this->merge(['course_is_free' => $this->courseIsFree]) ;
    }
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
                    $section = Section::where('id', $value)->first();

                    if (!$section) {
                        $fail('section id not found');
                        return;
                    }
                    if ($section->type != 'courses') {
                        $fail('section type is not course');
                        return;
                    }
                    $SectionStudent = SectionStudent::where('section_id', $value)
                        ->where('student_id', auth('sanctum')->id());

                    if ($SectionStudent->exists()) {
                        $fail(__('messages.student_already_has_the_course'));
                        return;
                    }

                    if($this->courseIsFree){
                        return ;
                    }

                    $code = PurchaseCode::where('code', request()->input('purchase_code'))
                        ->whereHas('sections', function ($q) {
                            $q->where('sections.id', $this->section_id);
                        })
                        ->first();

                    if (!$code) {
                        $fail(__('messages.this_code_not_for_this_section'));
                    }
                }
            ],
            'purchase_code' => [
                Rule::requiredIf( !$this->courseIsFree),
                'string',
                function ($attribute, $value, $fail) {
                    $code = PurchaseCode::where('usage_limit', '>', 0)
                        ->where('expire_date', '>', Carbon::now())
                        ->where('code', $value)->first();
                    if (!$code) {
                        $fail(__('messages.the_provided_purchase_code_is_invalid.'));
                    }
                },
            ],
        ];
    }
}