<?php

namespace App\Http\Requests\Api\Admin\Section\Teacher;

use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Psy\TabCompletion\Matcher\ConstantsMatcher;

class OpenSectionTeacherRequest extends FormRequest
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
            'teacher_id' => [
                'bail',
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $teacher = User::with(['roles'])
                        ->where('id', $value)
                        ->first();

                    if (!$teacher) {
                        $fail('teacher not found');
                        return;
                    }

                    if (!$teacher->hasRole(Constants::TEACHER_ROLE)) {
                        $fail('id is not teacher');
                        return;
                    }
                }
            ],


            'section_id' => [
                    'bail',
                    'required',
                    'integer',
                    function ($attribute, $value, $fail) {
                        $section = Section::find($value);

                        if (!$section) {
                            $fail("section with id : $value does not exists");
                            return;
                        }

                        $type = Constants::SECTION_TYPE_SUPER;

                        if ($section->type != $type) {
                            $fail("section has the type : $section->type , but only type : $type is accepted");
                            return;
                        }
                    },
                    Rule::exists('sections', 'id')
                        ->whereNull('deleted_at'),

                    Rule::unique('course_teacher', 'course_id')
                        ->where('teacher_id', $this->get('teacher_id')),
                ],
        ];
    }
}
