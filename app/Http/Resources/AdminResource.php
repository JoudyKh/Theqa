<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
 * @OA\Schema(
 *     schema="AdminResource",
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
class AdminResource extends JsonResource
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
        $roles = $this->whenLoaded('roles' , fn()=>$this->roles , null) ;
        
        return [
            'id' => $this->id ,
            'full_name' => $this->full_name ,
            "first_name" => $this->first_name ,
            "last_name" => $this->last_name ,
            "username" => $this->username ,
            'description' => $this->description ,
            "email" => $this->email ,
            "phone_number" => $this->phone_number ,
            "email_verified_at" => $this->email_verified_at ,
            "last_active_at" => $this->last_active_at ,
            "image" => $this->image ?? $this->images->pluck('image')->first() ,
            'can_delete' => ! collect($roles)->contains(function($item){
                return $item->name == Constants::SUPER_ADMIN_ROLE ;
            }),
            'roles' => $roles ,
        ] ;
    }
}
