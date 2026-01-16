<?php

namespace App\Http\Requests\Api\App\StudentExam;

use App\Models\StudentExam;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Schema(
 *     schema="CreateStudentExamRequest",
 *     type="object",
 *     @OA\Property(property="lesson_id", type="integer", nullable=true, description="The ID of the lesson. Required if section_id is present."),
 *     @OA\Property(property="section_id", type="integer", description="The ID of the section. Required either on its own or along with lesson_id.")
 * )
 */
class CreateStudentExamRequest extends FormRequest
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

        ];
    }

    protected function prepareForValidation()
    {
        $exam = $this->route('exam');

        if ($exam && $exam->expires_at && $exam->expires_at <= now()) {
            $validator = Validator::make([], []);
            $validator->errors()->add('exam', 'The selected exam has expired.');
            throw new ValidationException($validator);
        }
    }
}