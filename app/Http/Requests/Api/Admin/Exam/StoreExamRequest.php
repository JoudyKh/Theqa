<?php

namespace App\Http\Requests\Api\Admin\Exam;

use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Question;
use Illuminate\Validation\Rule;
use App\Rules\ExistsMultiTables;
use App\Constants\MorphConstants;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Http\FormRequestrk\isJson;

/**
 * @OA\Schema(
 *     schema="StoreExamRequest",
 *     type="object",
 *     @OA\Property(property="degree", type="integer"),
 *     @OA\Property(property="random_questions_max", type="integer"),
 *     @OA\Property(property="is_free", type="integer", enum={0, 1}, description="Whether the lesson is free or not (0 for no, 1 for yes)", example=1),
 *     @OA\Property(
 *        property="image",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *        property="solution_file",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="lesson_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="section_id",
 *         type="integer"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the exam",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="minutes",
 *         type="integer",
 *         description="Duration of the exam in minutes",
 *         example=60
 *     ),
 *     @OA\Property(
 *         property="pass_percentage",
 *         type="integer",
 *         description="Percentage required to pass the exam",
 *         example=75
 *     ),
 *
 *     @OA\Property(
 *         property="questions_count",
 *         type="integer",
 *         description="Number of questions to generate",
 *         example=75
 *     ),
 *
 *     @OA\Property(
 *         property="auto_generate_questions",
 *         type="boolean",
 *         description="Whether to generate questions automatically",
 *         example=true
 *     ),
 *
 *     @OA\Property(
 *         property="existing_questions[]",
 *         type="array",
 *         description="Array of IDs of existing questions to include in the exam",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="lessons_ids[]",
 *         type="array",
 *         description="Array of IDs of existing lessons to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="units_ids[]",
 *         type="array",
 *         description="Array of IDs of existing units to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="subjects_ids[]",
 *         type="array",
 *         description="Array of IDs of existing subjects to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="questions[0][video]",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *         property="questions[0][image]",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *     @OA\Property(
 *         property="questions[0][note_image]",
 *         type="string",
 *         nullable=true,
 *         format="binary"
 *     ),
 *
 *     @OA\Property(property="questions[0][text]", type="string", example="What is the capital of France?"),
 *     @OA\Property(property="questions[0][note]", type="string", example="This question is related to geography."),
 *     @OA\Property(property="questions[0][degree]", type="integer", example=1),
 *     @OA\Property(property="questions[0][page_number]", type="integer", example=1),
 *     @OA\Property(property="questions[0][options][0][name]", type="string", example="false"),
 *     @OA\Property(property="questions[0][options][0][is_true]", type="integer", example=0),
 *     @OA\Property(property="questions[0][options][1][name]", type="string", example="true"),
 *     @OA\Property(property="questions[0][options][1][is_true]", type="integer", example=1)
 * )
 * @OA\Schema(
 *     schema="GenerateExamRequest",
 *     type="object",
 *     @OA\Property(property="degree", type="integer"),
 *     @OA\Property(
 *         property="name",
 *         type="string"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the exam",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="minutes",
 *         type="integer",
 *         description="Duration of the exam in minutes",
 *         example=60
 *     ),
 *     @OA\Property(
 *         property="pass_percentage",
 *         type="integer",
 *         description="Percentage required to pass the exam",
 *         example=75
 *     ),
 *
 *     @OA\Property(
 *         property="questions_count",
 *         type="integer",
 *         description="Number of questions to generate",
 *         example=75
 *     ),
 *
 *     @OA\Property(
 *         property="lessons_ids[]",
 *         type="array",
 *         description="Array of IDs of existing lessons to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="units_ids[]",
 *         type="array",
 *         description="Array of IDs of existing units to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="subjects_ids[]",
 *         type="array",
 *         description="Array of IDs of existing subjects to generate questions from",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     ),
 * )
 */

class StoreExamRequest extends FormRequest
{
    public function prepareForValidation()
    {
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
            'degree' => ['numeric'],
            'random_questions_max' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_free' => ['boolean'],

            'solution_file' => ['nullable', 'file', 'mimes:pdf'],

            'image' => ['required', 'image', 'mimes:png,jpg,jpeg'],
            'name' => ['required', 'string', 'max:255'],

            'section_id' => [
                'bail',
                'integer',
                Rule::exists('sections', 'id')
                    ->whereNull('deleted_at'),
            ],
            'lesson_id' => [
                'bail',
                'integer',
                Rule::exists('lessons', 'id'),
            ],

            'description' => ['nullable', 'string'],
            'minutes' => ['required', 'integer', 'min:1', 'max:300'],
            'pass_percentage' => ['required', 'integer', 'min:0', 'max:100'],

            'existing_questions' => ['array'],
            'existing_questions.*' => ['required', 'integer', 'distinct', 'exists:questions,id'],

            'questions' => ['array', 'distinct'],

            'questions.*.text' => ['required'],
            'questions.*.degree' => ['integer'],
            'questions.*.note' => ['nullable'],

            'questions.*.video' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!(is_string($value) || ($value instanceof \Illuminate\Http\UploadedFile && $value->getClientMimeType() === 'video/mp4'))) {
                        $fail('The ' . $attribute . ' must be a string or an mp4 file.');
                    }
                },
            ],
            'questions.*.image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
            'questions.*.note_image' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg',
                'max:2048',
            ],
            'questions.*.page_number' => ['nullable', 'integer'],

            'questions.*.options' => ['required', 'array', 'min:2', 'max:10'],

            'questions.*.options.*.name' => ['required'],
            'questions.*.options.*.is_true' => ['required', 'boolean'],

            'auto_generate_questions' => ['boolean'],
            'questions_count' => ['required_with:auto_generate_questions', 'integer', 'min:1', 'max:1000'],
            'lessons_ids' => ['array', 'min:1'],
            'lessons_ids.*' => ['integer', 'exists:lessons,id'],
            'subjects_ids' => ['array', 'min:1'],
            'subjects_ids.*' => ['integer', 'exists:sections,id'],
            'units_ids' => ['array', 'min:1'],
            'units_ids.*' => ['integer', 'exists:sections,id'],
            'expires_at' => ['date_format:Y-m-d H:i:s'],

        ];
    }
}
