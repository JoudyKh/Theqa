<?php

namespace App\Http\Resources;

use App\Constants\Constants;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="QuestionResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="Unique identifier for the question"),
 *     @OA\Property(property="text", type="string", description="Text of the question"),
 *     @OA\Property(property="note", type="string", description="Note of the question"),
 *     @OA\Property(property="degree", type="integer", description="Degree or weight of the question"),
 *     @OA\Property(
 *         property="options",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/OptionResource")
 *     )
 * )
 */
class QuestionResource extends JsonResource
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
        $options = $this->whenLoaded('options', OptionResource::collection($this->options), null);

        $chosen_and_true = false;

        foreach ($options ?? [] as $option) {
            if ($option && $option['is_true'] && $option['is_chosen']) {
                $chosen_and_true = true;
                break;
            }
        }

        $data = [
            'id' => $this->id,
            'page_number' => $this->page_number,
            'text' => $this->text,
            'degree' => $this->degree,
            'video' => $this->video,
            'image' => $this->image,
            'note_image' => $this->note_image,
            'note' => $this->note,
            'options' => $options,
            'chosen_and_true' => $chosen_and_true ?? null,
            'deleted_at' => $this->deleted_at,
        ];

        if (app()->bound('is_admin') ) {
            $data['has_been_chosen_by_student'] = in_array($this->id, request()->get('chosen_questions', []));
        }

        return $data;
    }
}
