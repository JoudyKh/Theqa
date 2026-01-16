<?php

namespace App\Http\Requests\Api\Admin\Exam;

use App\Models\Exam;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\Question;
use App\Models\StudentAnswer;
use Illuminate\Validation\Rule;
use App\Rules\ExistsMultiTables;
use App\Constants\MorphConstants;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     title="UpdateExamRequest",
 *     description="Request body for updating an existing exam",
 *     type="object",
 *
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
 *     ),@OA\Property(
 *         property="section_id",
 *         type="integer"
 *     ),
 *
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Optional description of the exam",
 *         example="Updated exam description",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="minutes",
 *         type="integer",
 *         description="Duration of the exam in minutes",
 *         example=45
 *     ),
 *     @OA\Property(
 *         property="pass_percentage",
 *         type="integer",
 *         description="The minimum percentage required to pass the exam",
 *         example=70
 *     ),
 *     @OA\Property(property="questions[0][text]", type="string", example="What is the capital of France?"),
 *     @OA\Property(property="questions[0][note]", type="string", example="This question is related to geography."),
 *     @OA\Property(property="questions[0][degree]", type="integer", example=1),
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
 *     @OA\Property(property="questions[0][page_number]", type="integer", example=1),
 *     @OA\Property(property="questions[0][options][0][name]", type="string", example="false"),
 *     @OA\Property(property="questions[0][options][0][is_true]", type="integer", example=0),
 *     @OA\Property(property="questions[0][options][1][name]", type="string", example="true"),
 *     @OA\Property(property="questions[0][options][1][is_true]", type="integer", example=1),
 *     @OA\Property(
 *         property="trash_questions",
 *         type="array",
 *         description="List of IDs of questions to be removed from the exam",
 *         @OA\Items(
 *             type="integer",
 *             example=1
 *         ),
 *         nullable=true
 *     )
 * )
 */
class UpdateExamRequest extends FormRequest
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
        $exam = $this->route('exam');

        $existingQuestionsCount = $exam->questions()->count();

        return [
            'degree' => ['numeric'],

            'random_questions_max' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_free' => ['boolean'],

            'solution_file' => ['nullable', 'file', 'mimes:pdf'],

            'image' => ['nullable', 'image', 'mimes:png,jpg,jpeg'],
            'name' => ['string', 'max:255'],

            'exam_order_replacement_id' => [
                'integer',
                Rule::notIn([$exam->id]),
                Rule::exists('exams', 'id')
                    ->whereNull('deleted_at')
                    ->where('model_id', $exam->model_id)
                    ->where('model_type', $exam->model_type),
            ],

            'section_id' => [
                'bail',
                'integer',
                Rule::exists('sections', 'id')
                    ->whereNull('deleted_at'),
            ],
            'lesson_id' => [
                'bail',
                'integer',
                Rule::exists('lessons', 'id')
                    ->whereNull('deleted_at'),
            ],

            'description' => ['nullable', 'string'],
            'minutes' => ['integer', 'min:0', 'max:300'],
            'pass_percentage' => ['integer', 'min:0', 'max:100'],

            'existing_questions' => ['array'],
            'existing_questions.*' => [
                'required',
                'integer',
                'distinct',
                'exists:questions,id',
                Rule::unique('exam_question', 'question_id')
                    ->where('exam_id', $exam->id),
            ],

            'questions' => ['array'],
            'questions.*.text' => ['required'],
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

            'questions.*.degree' => ['integer'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:10'],
            'questions.*.options.*.name' => ['required'],
            'questions.*.options.*.is_true' => ['required', 'boolean'],

            'trash_questions' => ['array'],
            'trash_questions.*' => [
                'integer',
                Rule::exists('exam_question', 'question_id')
                    ->where('exam_id', $exam->id),
                function ($attribute, $value, $fail) use ($exam) {
                    $student_answers = StudentAnswer::query();

                    $student_answers->where('question_id', $value);

                    if ($student_answers->exists()) {
                        $fail(__('messages.student_already_choose_this_question'));
                    }
                }
            ],
            'expires_at' => ['nullable', 'date_format:Y-m-d H:i:s'],

        ];
    }
}
