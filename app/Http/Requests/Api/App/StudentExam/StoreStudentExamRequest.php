<?php

namespace App\Http\Requests\Api\App\StudentExam;

use App\Models\Exam;
use App\Models\User;
use App\Models\Lesson;
use App\Models\Option;
use App\Models\Section;
use App\Models\Question;
use App\Models\StudentExam;
use App\Constants\Constants;
use App\Models\ExamQuestion;
use App\Models\SectionStudent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;
use App\Services\App\StudentExam\StudentExamService;


/**
 * @OA\Schema(
 *     schema="StoreStudentExamRequest",
 *     type="object",
 *     required={"answers" },
 *     @OA\Property(
 *         property="answers",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             required={"question_id", "option_id"},
 *             @OA\Property(
 *                 property="question_id",
 *                 type="integer",
 *                 example=1,
 *                 description="The ID of the question."
 *             ),
 *             @OA\Property(
 *                 property="option_id",
 *                 type="integer",
 *                 example=2,
 *                 description="The ID of the selected option."
 *             )
 *         )
 *     )
 * )
 */
class StoreStudentExamRequest extends FormRequest
{
    protected ?User $currUser = null;
    protected ?Exam $exam = null;
    protected ?StudentExam $studentExam = null;
    public function prepareForValidation()
    {
        ////////////////////////////////////////////////////////////////////////////////////////////////////
        //if option_id = -1 then the user did not any option in the question

        $this->merge([
            'answers' => collect($this->input('answers'))->map(function ($answer) {
                if (isset($answer['option_id']) && $answer['option_id'] == -1) {
                    $answer['option_id'] = null;
                }
                return $answer;
            })->toArray(),
        ]);

        ////////////////////////////////////////////////////////////////////////////////////////////////////

        $this->exam = $this->route('exam');

        $this->currUser = User::where('id', auth('sanctum')->id())
            ->first();

        //check if there is questions attached to student exam
        if (!$this->currUser) {
            $this->mergeIfMissing(['student_state' => 'testing']);
            return;
        }

        if ($this->currUser) {
            $studentExam = StudentExam::with(['student_answers', 'questions.options'])->where([
                'exam_id' => $this->exam->id,
                'student_id' => $this->currUser->id,
            ])
                ->orderByDesc('created_at')
                ->first();

            if ($studentExam?->student_answers?->first()) {
                $this->studentExam = $studentExam;

                app()->instance(
                    'questions',
                    $this->studentExam->questions
                );
                $this->mergeIfMissing(['student_state' => 'solving']);
            }
        }

        if ($this->missing('student_state') and $this->currUser) {
            $modelIsFree = false;
            $studentIsSubscribed = false;

            if ($this->exam->model_type == Lesson::class) {
                $lesson = Lesson::findOrFail($this->exam->model_id);

                $modelIsFree = $lesson->is_free;
                $studentIsSubscribed = Section::isSubscribed($lesson->section_id);
            } else {
                $modelIsFree = $this->exam->is_free;
                $studentIsSubscribed = Section::isSubscribed($this->exam->model_id);
            }

            if ($studentIsSubscribed) {
                $this->mergeIfMissing(['student_state' => 'solving']);
            }

            if ($modelIsFree and !$studentIsSubscribed) {
                $this->mergeIfMissing(['student_state' => 'testing']);
            }
        }

        $this->mergeIfMissing(['student_state' => 'solving']);

        if ($this->get('student_state') == 'solving' and !app()->bound('questions')) {
            app()->instance(
                'questions',
                $this->exam->questions,
            );
        }
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
            'student_state' => ['string', 'in:testing,solving'],
            'answers' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {

                    if (app()->bound('questions')) {
                        $questionCount = count(app('questions'));

                        $answerCount = count(request()->input('answers'));

                        if ($questionCount != $answerCount) {
                            $fail('messages.question_count_not_equal_answer_count');
                            return;
                        }
                    }
                }
            ],
            'answers.*.question_id' => [
                'bail',
                'required',
                'integer',
                'distinct',
                function ($attribute, $value, $fail) {

                    if (
                        !(
                            app()->bound('questions')
                            and
                            app('questions')->contains('id', $value)
                        )
                        and
                        !$this->exam->questions->contains('id', $value)
                    ) {
                        $fail('question with id : ' . $value . ' not found');
                        return;
                    }
                }
            ],
            'answers.*.option_id' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    $questionId = $this->input(str_replace('option_id', 'question_id', $attribute));

                    $option = null;

                    // Check if the `StudentExam` has existing answers
                    if (app()->bound('questions')) {
                        // Get the question from `StudentExam`'s loaded `questions`
                        $question = app('questions')?->first(fn($item) => $item->id == $questionId);

                        if ($question) {
                            // Search for the option in the question's options
                            $option = $question->options->first(fn($opt) => $opt->id == $value);
                        }
                    } else {
                        // Fallback to the `Exam` questions
                        $question = $this->exam->questions->first(fn($item) => $item->id == $questionId);

                        if ($question) {
                            // Search for the option in the question's options
                            $option = $question->options->first(fn($opt) => $opt->id == $value);
                        }
                    }

                    // Validation: Option must exist and belong to the question
                    if (!$option) {
                        $fail("Option with ID: {$value} is not valid for Question ID: {$questionId}");
                    }
                }
            ],

        ];
    }
}
