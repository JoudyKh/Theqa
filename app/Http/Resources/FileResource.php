<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FileResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", description="ID of the file"),
 *     @OA\Property(property="path", type="string", description="Path of the file"),
 *     @OA\Property(property="name", type="string", description="Name of the file"),
 *     @OA\Property(property="url", type="string", description="URL of the file"),
 *     @OA\Property(property="type", type="string", description="MIME type of the file"),
 *     @OA\Property(property="extension", type="string", description="File extension"),
 *     @OA\Property(property="size", type="integer", description="Size of the file in bytes"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", description="Timestamp when the file was deleted")
 * )
 */
class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'path' => $this->path ,
            'name' => $this->name ,
            'url' => $this->url ,
            'type' => $this->type ,
            'extension' => $this->extension ,
            'size' => $this->size ,
            'deleted_at' => $this->deleted_at ,
        ];
    }
}
