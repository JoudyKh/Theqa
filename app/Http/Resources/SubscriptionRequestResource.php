<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\Section\CourseResource;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SubscriptionRequestResource",
 *     type="object",
 *     required={"id", "student_id", "exam_id", "start_date", "end_date", "created_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         example=1,
 *         description="The unique identifier of the student exam."
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
class SubscriptionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return \Illuminate\Pagination\AbstractPaginator|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
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
        return [
            'id' => $this->id,

            'coupon' => $this->whenLoaded('coupon', fn() => CouponResource::make($this->coupon), null),
            'section' => $this->whenLoaded('section', fn() => CourseResource::make($this->section), null),
            'student' => $this->whenLoaded('student', fn() => StudentResource::make($this->student), null),

            'reject_reason' => $this->reject_reason,
            
            'image' => $this->image,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}