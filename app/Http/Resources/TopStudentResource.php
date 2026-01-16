<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="TopStudentResource",
 *     type="object",
 *     title="Offer",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the offer."
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the offer."
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="A description of the offer."
 *     ),
 *     @OA\Property(
 *         property="degree",
 *         type="integer",
 *         description="The discount amount or percentage of the student."
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="url",
 *         description="URL of the image associated with the offer."
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the offer was soft deleted. Null if not deleted."
 *     )
 * )
 */
class TopStudentResource extends JsonResource
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
            'id' => $this->id ,
            'name' => $this->name ,
            'description' => $this->description ,
            'degree' => $this->degree ,
            'image' => $this->image ,
            'deleted_at' => $this->deleted_at ,
        ];
    }
}
