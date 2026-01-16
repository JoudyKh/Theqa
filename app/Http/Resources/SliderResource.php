<?php

namespace App\Http\Resources;

use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SliderResource",
 *     type="object",
 *     title="Slider",
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
class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id ,
            'type' => $this->type ,
            'deleted_at' => $this->deleted_at ,
        ];

        foreach(Slider::$types[$this->type]['attributes'] as $attribute){
            $data[$attribute] = $this->$attribute ;
        }

        return $data ;
    }
}
