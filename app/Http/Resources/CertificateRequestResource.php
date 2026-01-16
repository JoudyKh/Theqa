<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\Section\CourseResource;
use App\Http\Resources\Section\SectionResource;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="CertificateRequestResource",
 *     type="object",
 *     title="Offer",
 * )
 */
class CertificateRequestResource extends JsonResource
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
        return [
            'id'          => $this->id,
            'student_id'  => $this->student_id,
            'status'      => $this->status,
            'file'        => $this->file,
            'student'     => $this->whenLoaded('student' , fn()=>StudentResource::make($this->student) , null) ,
            'course'      => $this->whenLoaded('course',CourseResource::make($this->course) , null),
            'rejected_at' => $this->rejected_at,
            'accepted_at' => $this->accepted_at,
            'note'        => $this->note,
            'deleted_at'  => $this->deleted_at,
            'created_at'  => $this->created_at,
        ];
    }
}