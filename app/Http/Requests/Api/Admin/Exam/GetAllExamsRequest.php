<?php

namespace App\Http\Requests\Api\Admin\Exam;

use App\Models\Exam;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class GetAllExamsRequest extends FormRequest
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
            'type' => ['string' , Rule::in(Exam::types()->all())] ,
            'status' => ['string', 'in:solved,failed'],
            'is_solved' => ['boolean'],
            'student_id' => ['integer', 'exists:users,id'],
            'section_id' => ['integer',Rule::exists('sections' , 'id')->whereNull('deleted_at'),],
            'lesson_id' => ['integer', 'exists:lessons,id'],
            'without_model' => ['boolean'],
            'search' => ['string'],
            'exam_search' => ['string'],
            'generated_exams' => ['boolean'],
        ];
    }
}
