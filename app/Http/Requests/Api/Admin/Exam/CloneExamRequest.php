<?php

namespace App\Http\Requests\Api\Admin\Exam;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="CloneExamRequest",
 *     type="object",
 *     @OA\Property(
 *         property="clone_exam_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="target_exam_id",
 *         type="integer"
 *     )
 * )
 */

class CloneExamRequest extends FormRequest
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
            'clone_exam_id' => ['required' , 'integer' , Rule::exists('exams' , 'id')->whereNull('deleted_at'),] ,
            'target_exam_id' => ['integer' , Rule::exists('exams' , 'id')->whereNull('deleted_at'),] ,
        ];
    }
}
