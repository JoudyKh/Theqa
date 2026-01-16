<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OptionResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="Unique identifier for the option"),
 *     @OA\Property(property="name", type="string", description="Text of the option"),
 *     @OA\Property(property="is_true", type="boolean", description="Indicates if the option is the correct answer"),
 *     @OA\Property(property="is_chosen", type="boolean", description="Indicates if the option is the chosen by your answer"),
 * )
 */

class OptionResource extends JsonResource
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
        $data = [
            'id' => $this?->id,
            'name' => $this?->name,
        ];

        //i am admin
        if (request()->is('*admin*')) {
            $data['is_true'] = $this?->is_true;
            $data['is_chosen'] = null;

        }
        //i solved this exam ,, other wise no examResultDto would be found in the request
        else if (request()->has('examResultDto')) {
            $examResultDto = request()->get('examResultDto', null);

            $data['is_chosen'] = in_array($this?->id, $examResultDto['chosenOptionsId'] ?? []);
            //i am no body , just give me null
        } else {
            $data['is_true'] = null;
            $data['is_chosen'] = null;
        }

        return $data;
    }
}
