<?php

namespace App\Http\Requests\Api\Admin\Lesson;

use App\Models\Lesson;
use App\Rules\OneOrNone;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;


/**
 * @OA\Schema(
 *     schema="StoreLessonRequest",
 *     type="object",
 *     required={"name", "description", "time", "cover_image", "video_url", "is_free","section_id"},
 *     @OA\Property(property="is_free", type="integer", enum={0, 1}, description="Whether the lesson is free or not (0 for no, 1 for yes)", example=1),
 *     @OA\Property(property="name", type="string", description="Name of the lesson", example="Introduction to Laravel"),
 *     @OA\Property(property="description", type="string", description="Description of the lesson", example="A comprehensive guide to Laravel."),
 *     @OA\Property(property="time", type="string", description="Time associated with the lesson in HH:MM:SS format", example="01:30:00"),
 *     @OA\Property(property="cover_image", type="string", format="binary", description="Cover image for the lesson"),
 *     @OA\Property(property="video_url", type="string", format="uri", description="URL of the lesson video", example="https://example.com/video.mp4"),
 *     @OA\Property(
 *         property="files[]",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             format="binary"
 *         ),
 *         description="Array of files associated with the lesson"
 *     ),
 *     @OA\Property(
 *         property="exam_id",
 *         type="integer",
 *         description="ID of an existing exam in the database",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="section_id",
 *         type="integer",
 *         nullable=true,
 *         description="ID of the lesson section"
 *     ),
 *     @OA\Property(
 *         property="exam",
 *         type="object",
 *         description="New exam details to be created",
 *         @OA\Property(property="description", type="string", nullable=true, description="Description of the exam"),
 *         @OA\Property(property="minutes", type="integer", description="Duration of the exam in minutes", example=60),
 *         @OA\Property(property="pass_percentage", type="integer", description="Passing percentage for the exam", example=70, minimum=0, maximum=100),
 *         @OA\Property(
 *             property="existing_questions",
 *             type="array",
 *             description="IDs of existing questions to include in the exam",
 *             @OA\Items(
 *                 type="integer",
 *                 description="ID of an existing question",
 *                 example=1
 *             )
 *         ),
 *         @OA\Property(
 *             property="questions",
 *             type="array",
 *             description="Array of new questions for the exam",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="exam_order", type="integer", description="", example=10),
 *                 @OA\Property(property="text", type="string", description="Text of the question", example="What is Laravel?"),
 *                 @OA\Property(property="degree", type="integer", description="Degree/points assigned to the question", example=10),
 *                 @OA\Property(property="note", type="string", nullable=true, description="Additional notes for the question", example="This is a fundamental question."),
 *                 @OA\Property(
 *                     property="options",
 *                     type="array",
 *                     description="Options for the question",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="name", type="string", description="Option text", example="A PHP framework"),
 *                         @OA\Property(property="is_true", type="boolean", description="Whether this option is correct", example=true)
 *                     ),
 *                     minItems=2,
 *                     maxItems=10
 *                 )
 *             )
 *         )
 *     )
 * )
 */
class StoreLessonRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        if ($this->has('exam')) {
            $this->merge([
                'exam' => $this->input('exam')
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $parentSection = $this->route('parentSection');

        $this->merge(['section_id' => $parentSection?->id ?? $parentSection]);

        return [
            'is_free' => ['required', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['string', 'required'],
            'time' => ['regex:/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', 'required'],
            'cover_image' => ['image', 'mimes:png,jpg,jpeg', 'required'],
            'video_url' => ['string', 'url', 'required'],
            'files' => ['array', 'max:400'],
            'files.*' => ['file', 'mimes:pdf,zip,png,jpg,jpeg'],

            'exam_id' => [
                'nullable',
                'integer',
                Rule::exists('exams', 'id')
                    ->whereNull('deleted_at')
                    ->whereNull('model_id')
                    ->whereNull('model_type'),
            ],

            'exam' => [new OneOrNone('exam_id')],

            'exam.is_free' => ['boolean'],

            'exam.description' => ['nullable', 'string'],
            'exam.minutes' => [Rule::requiredIf(request()->has('exam')), 'integer'],
            'exam.pass_percentage' => [Rule::requiredIf(request()->has('exam')), 'integer', 'min:0', 'max:100'],

            'exam.existing_questions' => ['array'],
            'exam.existing_questions.*' => ['required', 'integer', 'distinct', 'exists:questions,id'],

            'exam.questions' => ['array',],

            'exam.questions.*.text' => ['required'],
            'exam.questions.*.degree' => ['integer'],
            'exam.questions.*.note' => ['nullable'],

            'exam.questions.*.options' => ['required', 'array', 'min:2', 'max:10'],

            'exam.questions.*.options.*.name' => ['required'],
            'exam.questions.*.options.*.is_true' => ['required', 'boolean'],

        ];
    }
}
