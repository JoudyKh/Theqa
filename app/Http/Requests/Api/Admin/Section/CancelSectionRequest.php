<?php

namespace App\Http\Requests\Api\Admin\Section;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class CancelSectionRequest extends FormRequest
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
            'student_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $student = User::with('roles')
                        ->where('id', $value)
                        ->first();

                    if (!$student) {
                        $fail('user with id ' . $value . ' not found');
                        return;
                    }

                    if (!$student->hasRole(Constants::STUDENT_ROLE)) {
                        $fail('user is not a student');
                        return;
                    }
                }
            ],
            'section_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists('sections', 'id')
                    ->whereNull('deleted_at'),
                Rule::exists('section_student', 'section_id')
                    ->where('student_id' , $this->input('student_id')),
            ],
        ];
    }
}
