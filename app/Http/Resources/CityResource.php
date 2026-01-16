<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="CityResource",
 *     type="object",
 *     title="City",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the governorate."
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the governorate."
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the governorate was soft deleted. Null if not deleted."
 *     )
 * )
 */
class CityResource extends JsonResource
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
            'students_count' => $this->students_count ,
            'governorate_id' => $this->governorate_id ,
            'governorate' => $this->whenLoaded('governorate' , fn()=>GovernorateResource::make($this->governorate) , null) ,
            'deleted_at' => $this->deleted_at ,
        ];
    }
}
