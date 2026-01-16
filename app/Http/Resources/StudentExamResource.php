<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="StudentExamResource",
 *     type="object",
 *     required={"id", "student_id", "exam_id", "start_date", "end_date", "created_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="The unique identifier of the student exam."
 *     ),
 *     @OA\Property(
 *         property="student_id",
 *         type="integer",
 *         example=123,
 *         description="The ID of the student who took the exam."
 *     ),
 *     @OA\Property(
 *         property="exam_id",
 *         type="integer",
 *         example=456,
 *         description="The ID of the exam."
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-08T10:00:00Z",
 *         description="The start date and time of the exam."
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-08T12:00:00Z",
 *         description="The end date and time of the exam."
 *     ),
 *     @OA\Property(
 *         property="student_answers",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/StudentAnswerResource")
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-08T09:00:00Z",
 *         description="The timestamp when the exam record was created."
 *     )
 * )
 */

class StudentExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'exam_degree' => $this->exam_degree,
            'exam_pass_percentage' => $this->exam_pass_percentage ,
            'exam_student_degree' => ($this->total_degree ? round(($this->degree / $this->total_degree) * $this->exam_degree) : null) ,
            'student_id' => $this->student_id ,
            'exam_id' => $this->exam_id ,
            'on_time' => $this->on_time ,
            'start_date' => $this->start_date ,
            'end_date' => $this->end_date ,
            'student_answers' => $this->whenLoaded('student_answers' , fn()=>StudentAnswerResource::collection($this->student_answers) , null) ,
            'degree' => $this->degree ,
            'total_degree' => $this->total_degree ,
            'student_percentage' => round( ( ($this->total_degree > 0) ? ($this->degree / $this->total_degree) : 0) * 100 , 2 ) ,
            'updated_at' => $this->updated_at ,
            'created_at' => $this->created_at ,
        ];
    }
}
