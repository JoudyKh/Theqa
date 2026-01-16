<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="BookResource",
 *     type="object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier of the book",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The name of the book",
 *         example="Introduction to Laravel"
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="uri",
 *         description="URL to the book's image",
 *         example="https://example.com/images/book.jpg"
 *     ),
 *     @OA\Property(
 *         property="file",
 *         type="string",
 *         format="uri",
 *         description="URL to the book's file",
 *         example="https://example.com/files/book.pdf"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="A brief description of the book",
 *         example="A comprehensive guide to Laravel."
 *     ),
 *     @OA\Property(
 *         property="deleted_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the book was soft deleted",
 *         example="2024-08-05T12:34:56Z",
 *         nullable=true
 *     )
 * )
 */
class BookResource extends JsonResource
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
            'image' => $this->image ,
            'file' => $this->file ,
            'description' => $this->description ,
            'deleted_at' => $this->deleted_at ,
        ];
    }
}
