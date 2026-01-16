<?php

namespace App\Http\Resources;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ExamResource",
 *     type="object",
 *     @OA\Property(property="id",type="integer",description="Unique identifier for the exam"),
 *     @OA\Property(property="description",type="string",description="Description of the exam"),
 *     @OA\Property(property="minutes",type="integer",description="Duration of the exam in minutes"),
 *     @OA\Property(property="question_count",type="integer",description="number of questions in the exam"),
 *     @OA\Property(property="pass_percentage",type="integer",description="Percentage required to pass the exam"),
 *     @OA\Property(
 *          property="questions",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/QuestionResource")
 *     ),
 * )
 */
class ExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Pagination\AbstractPaginator
     */
    public static function collection($data)
    {
        /*
            This simply checks if the given data is and instance of Laravel's paginator classes
            and if it is,
            it just modifies the underlying collection and returns the same paginator instance
        */
        if (is_a($data, \Illuminate\Pagination\AbstractPaginator::class)) {
            $data->setCollection(
                $data->getCollection()->map(function ($listing) {
                    return new static($listing);
                })
            );
            return $data;
        }

        return parent::collection($data);
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        //return the result key = null and if it success return the results
        //if there is a Dto

        //this could be in the lesson resource or here what is the best place ?
        //DTO  for data transfer obj
        $data = [];

        $examResultDto = (app()->bound('examResultDto') ? app('examResultDto') : null);

        if ($examResultDto) {
            //it may not be the exam that got loaded with the lesson
            $data = $examResultDto['exam'];
            $data['questions_count'] = $this->questions_count;
            $data['result'] = $examResultDto['result'];
            $data['studentExam'] = $examResultDto['studentExam'];

        } else {

            $data = [
                'solution_file' => $this->solution_file,
                'attempts_count' => $this->attempts_count,
                'id' => $this->id,
                'type' => $this->type,
                'degree' => $this->degree,
                'random_questions_max' => $this->random_questions_max,
                'name' => $this->name,
                'image' => $this->image,
                'is_free' => $this->is_free,
                'description' => $this->description,
                'model_type' => $this->model_type,
                'model_id' => $this->model_id,
                'minutes' => $this->minutes,
                'exam_order' => $this->exam_order,
                'pass_percentage' => $this->pass_percentage,
                'questions_count' => $this->questions_count,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'expires_at' => $this->expires_at,
                'questions' => $this->paginatedQuestions ?? $this->whenLoaded('questions', fn() => QuestionResource::collection($this->questions), null),
            ];

            //just for the mobile guys
            $data['result'] = [
                "pass" => null,
                "pass_percentage" => null,
                "student_percentage" => null,
                "total_degree" => null,
                "student_degree" => null,
                "attempts_count" => null,
                "exam_degree" => $this->degree,
                "exam_pass_percentage" => $this->pass_percentage,
                "exam_student_degree" => null,
            ];

            $data['studentExam'] = $this->whenLoaded('studentExams', fn() => StudentExamResource::make($this->studentExams?->first()), fn() => [
                "id" => null,
                "student_id" => null,
                "exam_id" => null,
                "start_date" => null,
                "end_date" => null,
                "degree" => null,
                "total_degree" => null,
                "created_at" => null,
                "updated_at" => null,
                "exam_degree" => null,
                "exam_pass_percentage" => null,
                "student_percentage" => null,
            ]);


        }

        if (!$data['attempts_count']) {
            $data['attempts_count'] = $this->attempts_count;
        }

        if (request()->is('*admin*')) {
            $data['studentExams'] = $this->whenLoaded('studentExams', fn() => StudentExamResource::collection($this->studentExams), null);
        }

        $data['is_subscribed'] =
            $this->is_subscribed ??
            (app()->bound('subscribed_array') ? in_array($this->model_id, app('subscribed_array')) : null);

        $data['is_open'] = $this->is_open;
        $data['is_solved'] = $this->is_solved;

        $examsState = (app()->bound('examsState') ? app('examsState') : null);

        if (!is_null($examsState)) {
            $data['is_open'] = array_key_exists($this->id, $examsState);
            $data['is_solved'] = (array_key_exists($this->id, $examsState) and !is_null($examsState[$this->id]['degree']));

            if ($data['is_open'] and $this->is_subscribed === false) {
                $data['is_subscribed'] = true;
            }

        } elseif (!is_null($examResultDto)) {
            $data['is_open'] = true;
            $data['is_solved'] = $examResultDto['result']['pass'] !== null;
            $data['is_subscribed'] = true;
        }

        if (
            $this->is_free
            and (
                auth('sanctum')->guest()
                or
                (
                    $this->model_type == Section::class
                    and
                    (
                        $this->is_subscribed === false
                        or
                        (
                            app()->bound('subscribed_array')
                            and
                            (!in_array($this->model_id, app('subscribed_array')))
                        )
                    )
                )
            )
        ) {
            $data['is_open'] = true;
            $data['is_subscribed'] = false;
        }

        $data['next_exam_id'] = $this->next_exam_id ?? (app()->bound('next_exam_id') ? app('next_exam_id') : null);


        if (!$data['is_open']) {
            $data['is_open'] = $data['is_subscribed'];
        }

        if ($data['is_subscribed'] and app()->bound('first_exam_id') and $this->id == app('first_exam_id')) {
            $data['is_open'] = true;
        }

        $data['is_solving'] = $this->is_solving ?? (app()->bound('is_solving') ? app('is_solving') : null);
        $data['start_date'] = $this->start_date ?? (app()->bound('start_date') ? app('start_date') : null);
        $data['curr_date'] = $this->curr_date ?? (app()->bound('curr_date') ? app('curr_date') : null);
        $data['remaining_time'] = $this->remaining_time ?? (app()->bound('remaining_time') ? app('remaining_time') : null);

        $data['solution_file'] = null;

        if (request()->is('*admin*') or ($data['is_solved'] and $data['is_subscribed'])) {
            $data['solution_file'] = $this->solution_file;
        }

        return $data;
    }
}
