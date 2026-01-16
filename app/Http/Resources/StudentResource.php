<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="StudentResource",
 *     type="object",
 *     title="Teacher",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="first_name", type="string"),
 *     @OA\Property(property="last_name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="image", type="string"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time")
 * )
 */
class StudentResource extends JsonResource
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
            'devices_count' => $this->devices_count ,
            'is_banned' => $this->is_banned ,
            'is_active' => $this->is_active ,
            'id' => $this->id ?? '' ,
            "full_name" => $this?->full_name ?? '' ,
            "first_name" => $this?->first_name ?? '' ,
            "last_name" => $this?->last_name ?? '' ,
            "username" => $this?->username ?? '' ,

            "birth_date" => $this?->birth_date ,
            "location" => $this?->location ,

            'city_id' => $this->city_id ,
            'city' => $this->whenLoaded('city' , fn()=> CityResource::make($this->city) , null) ,

            'description' => $this?->description ?? '' ,
            "email" => $this?->email ?? '' ,

            "family_phone_number" => $this?->family_phone_number ?? '' ,
            "phone_number" => $this?->phone_number ?? '' ,
            "phone_number_country_code" => $this->phone_number_country_code ,
            "family_phone_number_country_code" => $this->family_phone_number_country_code ,

            "email_verified_at" => $this->email_verified_at ?? '' ,
            "last_active_at" => $this->last_active_at ?? '' ,
            'image' => $this->image , // $this->images()->pluck('image')->first()

            'total_avg' => round($this->total_avg ?? 0, 2) ,
            'rank' => $this->top_rank ,
            'solved_exams_count' => $this->solved_exams_count ,
            'failed_exams_count' => $this->failed_exams_count ,
            'exams_attempts_count_sum' => $this->attempts_count_sum ,
        ] ;
    }
}
