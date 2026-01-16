<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="StudentAnswerResource",
 *     type="object",
 *     required={"id", "student_id", "exam_id", "student_exam_id", "question_id", "option_id", "created_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="The unique identifier of the student answer."
 *     ),
 *     @OA\Property(
 *         property="student_id",
 *         type="integer",
 *         example=123,
 *         description="The ID of the student who answered the question."
 *     ),
 *     @OA\Property(
 *         property="exam_id",
 *         type="integer",
 *         example=456,
 *         description="The ID of the exam."
 *     ),
 *     @OA\Property(
 *         property="student_exam_id",
 *         type="integer",
 *         example=789,
 *         description="The ID of the student exam."
 *     ),
 *     @OA\Property(
 *         property="question_id",
 *         type="integer",
 *         example=1,
 *         description="The ID of the question."
 *     ),
 *     @OA\Property(
 *         property="option_id",
 *         type="integer",
 *         example=2,
 *         description="The ID of the selected option."
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-08-08T09:00:00Z",
 *         description="The timestamp when the student answer was created."
 *     )
 * )
 */

class StudentAnswerResource extends JsonResource
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
            'student_id' => $this->student_id ,
            'exam_id' => $this->exam_id ,
            'student_exam_id' => $this->student_exam_id ,
            'question_id' => $this->question_id ,
            'option_id' => $this->option_id ,
            'created_at' => $this->created_at ,
        ];
    }
}
