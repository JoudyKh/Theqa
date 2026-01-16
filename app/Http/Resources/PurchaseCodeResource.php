<?php

namespace App\Http\Resources;

use App\Http\Resources\Section\SectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PurchaseCodeResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code", type="string", example="ABCD1234"),
 *     @OA\Property(property="expire_date", type="string", format="date", example="2024-12-31"),
 *     @OA\Property(property="count", type="integer", example=10),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-07-21T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-07-21T10:00:00Z")
 * )
 */
class PurchaseCodeResource extends JsonResource
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
            'id' => $this->id,
            'code' => $this->code,
            'expire_date' => $this->expire_date,
            'usage_limit' => $this->usage_limit,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'courses' => $this->whenLoaded('sections' , fn()=>SectionResource::collection($this->sections) , null) ,
        ];
    }
}
