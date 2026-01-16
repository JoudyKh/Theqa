<?php

namespace App\Http\Requests\Api\Admin\Exam;

use App\Models\Exam;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BulkDeleteExamRequest",
 *     type="object",
 *     @OA\Property(property="trash_exams", type="string", example="1,2")
 * )
 */
class BulkDeleteExamRequest extends FormRequest
{
    public function prepareForValidation()
    {
        $this->merge([
            'trash_exams' => explode(',', $this->get('trash_exams')),
        ]);
    }
    /**
     * Determine if the exam is authorized to make this request.
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
            'trash_exams' => [
                'required',
                'array',
                'min:1'
            ],

            'trash_exams.*' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $exam = Exam::query()
                        ->withTrashed()
                        ->where('id', $value)
                        ->first();

                    if (!$exam) {
                        $fail('exam with id : ' . $value . ' not fount');
                        return;
                    }

                    if ($exam->deleted_at) {
                        $fail('exam with id : ' . $value . ' is soft deleted');
                        return;
                    }
                }
            ],

        ];
    }
}
