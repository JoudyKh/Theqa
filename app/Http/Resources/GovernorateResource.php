<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="GovernorateResource",
 *     type="object",
 *     title="Governorate",
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
class GovernorateResource extends JsonResource
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
        $studentsCount = $this->students_count;

        if (is_null($studentsCount) && $this->relationLoaded('cities')) {
            $studentsCount = $this->cities->sum('students_count');
        }
        
        return [
            'id' => $this->id ,
            'name' => $this->name ,
            'students_count' => $studentsCount ,
            'cities' => $this->whenLoaded('cities' , fn()=>CityResource::collection($this->cities) , null) ,
            'deleted_at' => $this->deleted_at ,
        ];
    }
}
