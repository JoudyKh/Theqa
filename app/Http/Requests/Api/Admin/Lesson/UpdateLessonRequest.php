<?php

namespace App\Http\Requests\Api\Admin\Lesson;

use App\Models\Exam;
use App\Models\Lesson;
use App\Rules\OneOrNone;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateLessonRequest",
 *     type="object",
 *     @OA\Property(property="name", type="string", description="Name of the lesson", example="Updated Lesson Name"),
 *     @OA\Property(property="is_free", type="integer", enum={0, 1}, description="Whether the lesson is free or not (0 for no, 1 for yes)", example=1),
 *     @OA\Property(property="description", type="string", description="Description of the lesson", example="Updated lesson description"),
 *     @OA\Property(property="time", type="string", description="Time associated with the lesson in HH:MM:SS format", example="02:00:00"),
 *     @OA\Property(property="video_url", type="string", format="uri", description="URL of the lesson video", example="https://example.com/updated-video.mp4"),
 *     @OA\Property(
 *         property="files",
 *         type="array",
 *         @OA\Items(
 *             type="string",
 *             format="binary"
 *         ),
 *         description="Array of files associated with the lesson"
 *     ),
 *     @OA\Property(
 *         property="trash_files",
 *         type="array",
 *         @OA\Items(
 *             type="integer",
 *             description="IDs of files to be removed",
 *             example=1
 *         ),
 *         description="Array of IDs of files associated with the lesson to be removed"
 *     ),
 *     @OA\Property(
 *         property="exam_id",
 *         type="integer",
 *         nullable=true,
 *         description="ID of an existing exam to associate with the lesson"
 *     ),
 *     @OA\Property(
 *         property="section_id",
 *         type="integer",
 *         description="ID of the lesson section"
 *     ),
 *     @OA\Property(
 *         property="exam",
 *         type="array",
 *         nullable=true,
 *         description="New exam details to be created",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="description", type="string", nullable=true, description="Description of the exam"),
 *             @OA\Property(property="minutes", type="integer", description="Duration of the exam in minutes", example=60),
 *             @OA\Property(property="pass_percentage", type="integer", description="Passing percentage for the exam", example=70, minimum=0, maximum=100),
 *             @OA\Property(
 *                 property="existing_questions",
 *                 type="array",
 *                 @OA\Items(
 *                     type="integer",
 *                     description="IDs of existing questions to include in the exam",
 *                     example=1
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="questions",
 *                 type="array",
 *                 description="Array of new questions for the exam",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="text", type="string", description="Text of the question", example="What is Laravel?"),
 *                     @OA\Property(property="degree", type="integer", description="Degree/points assigned to the question", example=10),
 *                     @OA\Property(property="note", type="string", nullable=true, description="Additional notes for the question", example="This is a fundamental question."),
 *                     @OA\Property(
 *                         property="options",
 *                         type="array",
 *                         description="Options for the question",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="name", type="string", description="Option text", example="A PHP framework"),
 *                             @OA\Property(property="is_true", type="boolean", description="Whether this option is correct", example=true)
 *                         ),
 *                         minItems=2,
 *                         maxItems=10
 *                     )
 *                 )
 *             )
 *         )
 *     )
 * )
 */

class UpdateLessonRequest extends FormRequest
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
        $lesson = $this->route('lesson') ;
        
        $parentSection = $this->route('parentSection') ;

        return [
            'lesson_order_replacement_id' => [
                'integer',
                Rule::notIn([$lesson->id]),
                Rule::exists('lessons' , 'id')
                ->whereNull('deleted_at')
                ->where('section_id' , $lesson->section_id) ,
            ] ,
            
            'name' => ['string' , 'max:255'],
            'is_free' => ['boolean'] ,
            'description' => ['string'],
            'time' => [
                'nullable',
                'regex:/^([01]\d|2[0-3]):([0-5]\d):([0-5]\d)$/', // HH:MM:SS format
            ],
            'video_url' => ['string' , 'url'],
            'files' => ['array' , 'max:400'],
            'files.*' => ['file' ] ,

            'trash_files' => ['array' , 'max:400'],
            'trash_files.*' => [
                'integer' ,
                Rule::exists('files' , 'id')
                ->where('model_id' , $lesson->id)
                ->where('model_type' , Lesson::class) ,
            ],


            'exam_id' => [
                'nullable',
                'integer' ,
                'exists:exams,id' ,
                Rule::exists('exams' , 'id')
                ->whereNull('deleted_at')
                ->whereNull('model_id')
                ->whereNull('model_type') ,
            ] ,
            
            'exam' => ['array' , new OneOrNone('exam_id')] ,

            'exam.exam_order' => [
                'integer',
                Rule::unique('exams', 'exam_order')
                    ->whereNull('deleted_at'),
            ],

            'exam.is_free' => ['boolean'],
            
            'exam.description' => ['nullable', 'string'],
            'exam.minutes' => [Rule::requiredIf(request()->has('exam')) , 'integer', 'max:300'],
            'exam.pass_percentage' => [Rule::requiredIf(request()->has('exam')) , 'integer', 'min:0', 'max:100'],

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
