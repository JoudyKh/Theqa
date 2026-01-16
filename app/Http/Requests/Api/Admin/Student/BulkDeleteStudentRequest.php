<?php

namespace App\Http\Requests\Api\Admin\Student;

use App\Models\User;
use App\Constants\Constants;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="BulkDeleteStudentRequest",
 *     type="object",
 *     @OA\Property(property="trash_students", type="string", example="1,2")
 * )
 */
class BulkDeleteStudentRequest extends FormRequest
{

    public function prepareForValidation()
    {
        $this->merge([
            'trash_students' => explode(',', $this->get('trash_students')),
        ]);
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
            'trash_students' => [
                'required',
                'array',
                'min:1'
            ],

            'trash_students.*' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $student = User::with('roles')
                        ->withTrashed()
                        ->where('id', $value)
                        ->first();

                    if (!$student) {
                        $fail('user with id : ' . $value . ' not fount');
                        return;
                    }

                    if ($student->deleted_at) {
                        $fail('user with id : ' . $value . ' is soft deleted');
                        return;
                    }

                    if (!$student->hasRole(Constants::STUDENT_ROLE)) {
                        $fail('user with id : ' . $value . ' is not a student ');
                        return;
                    }
                }
            ],

        ];
    }
}
